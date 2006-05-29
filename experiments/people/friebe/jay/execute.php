<?php
  require('lang.base.php');
  uses(
    'net.xp_framework.tools.vm.OpcodeHandler', 
    'net.xp_framework.tools.vm.PNode', 
    'net.xp_framework.tools.vm.VNode', 
    'util.cmd.Console', 
    'io.File', 
    'util.profiling.Timer',
    'io.FileUtil'
  );
  define('MODIFIER_NATIVE', 8);   // See lang.XPClass

  class ObjectInstance extends Object {
    var $id= NULL;
    
    function __construct($id) {
      $this->id= $id;
    }
  }
  
  // {{{ &lang.Object newinstance(string class, string bytes)
  //     Instance creation "expression"
  function &newinstance($class, $bytes) {
    static $i= 0;

    if (!class_exists($class)) {
      xp::error(xp::stringOf(new Error('Class "'.$class.'" does not exist')));
      // Bails
    }

    $name= $class.'�'.++$i;
    xp::registry('class.'.strtolower($name), $name);
    
    $c= $class;
    while ($c= get_parent_class($c)) {
      if ('interface' != $c) continue;
      
      // It's an interface
      eval('class '.$name.' extends Object '.$bytes);
      implements($name.'.class.php', $class);
      return new $name();
    }
    
    // It's a class
    eval('class '.$name.' extends '.$class.' '.$bytes);
    return new $name();
  }
  // }}}
  
  // {{{ &OpcodeHandler opcode(string bytes)
  //     Creates an opcode handler
  function &opcode($bytes) {
    return newinstance('OpcodeHandler', '{
      function handle(&$context, &$node) {
        '.$bytes.'
      }
    }');
  }
  // }}}
  
  function error($level, $message) {
    switch ($level) {
      case E_ERROR:
      case E_CORE_ERROR:
      case E_COMPILE_ERROR:
        xp::error($message);
        // Bails out
    }
    echo '*** ', $message, "\n";
  }
  
  function memberhash(&$statements) {
    $hash= array();
    
    for ($i= 0, $s= sizeof($statements); $i < $s; $i++) {
      if (is_a($statements[$i], 'InvokeableDeclarationNode')) {
        $hash[$statements[$i]->name]= &$statements[$i];   // FIXME: Overloading
      } else if (is_a($statements[$i], 'MemberDeclarationListNode')) {
        foreach ($statements[$i]->members as $member) {   // FIXME: Creating tons of lists here...
          $hash['$'.$member->args[0]]= &new MemberDeclarationListNode($statements[$i]->modifiers, array(&$member));
        }
      }
    }
    return $hash;
  }
  
  function loadclass($name, &$context) {
    // FIXME: Use classpath instead of hardcoding dirname(__FILE__)/rte/
    $filename= dirname(__FILE__).'/rte/'.strtr($name, '~', DIRECTORY_SEPARATOR).'.xpc';
    $nodes= unserialize(FileUtil::getContents(new File($filename)));
    execute($nodes, $context);
  }
  
  // FIXME: Compile-time?
  function declareclass(&$node, &$context) {
    if (isset($context['classes'][$node->name])) {
      error(E_ERROR, 'Cannot redeclare class '.$node->name);
    }
    
    $context['classes'][$node->name]= &$node;
    // $DEBUG= $node->name == 'xp~lang~SystemExit';
    if ($node->extends) {
      if (!$context['classes'][$node->extends]) {
        error(E_ERROR, 'Cannot inherit '.$node->name.' from non-existant class '.$node->extends);
        // Bails
      }

      $hash= memberhash($context['classes'][$node->name]->statements);
      $phash= memberhash($context['classes'][$node->extends]->statements);
      
      // Method inheritance
      foreach (array_keys($phash) as $key) {
        if (isset($hash[$key])) {   // Overwritten
          continue;   
        }

        // Generate declaring class member: FIXME: Needs to be done for all members,
        // not only inherited ones!
        $declaration= &$phash[$key];
        if (!isset($declaration->declaring)) {
          $declaration->declaring= $node->extends;
        }
        // DEBUG Console::writeLine('Inherit ', $node->extends, '::', $declaration->toString(), ' to ', $node->name);
        $context['classes'][$node->name]->statements[]= &$declaration;
      }
    }
    
    if (!empty($node->implements)) foreach ($node->implements as $iface) {
      if (!$context['classes'][$iface]) {
        error(E_ERROR, 'Class '.$node->name.' cannot implement non-existant class '.$iface);
        // Bails
      }
    
      $hash= memberhash($context['classes'][$node->name]->statements);
      $ihash= memberhash($context['classes'][$iface]->statements);
      foreach ($ihash as $key => $declaration) {
        if (!isset($hash[$key])) error(E_ERROR, $declaration->toString().' not implemented by '.$node->name);

        // DEBUG Console::writeLine($node->name, ' implements ', $declaration->toString());
      }
    }
  }
  
  function fetchfrom($storage, $id, $name, &$context) {
    if (!array_key_exists($id, $storage)) {
      error(E_NOTICE, 'Undefined '.$name.' '.$id.' in '.$context['__name']);
      return NULL;
    }
    
    return $storage[$id];
  }
  
  function fetch(&$var, &$context) {
    $value= fetchfrom($context['variables'], $var->name, 'variable', $context);
          
    // Lookup variable contents
    if ($var->offset) {
      return $value[$var->offset];
    } else {
      return $value;
    }
  }
  
  function &member($class, $name, &$context) {
    foreach ($context['classes'][$class]->statements as $decl) {
      if (!is_a($decl, 'MemberDeclarationListNode')) continue;

      // TODO: Check visibility

      // Find the member
      foreach ($decl->members as $member) {
        if ($member->args[0] != '$'.$name) continue;
        
        return $member;
      }
    }
    return NULL;
  }
  
  function set(&$var, $value, &$context) {
    if (is_a($var, 'ObjectReferenceNode')) {
      $pointer= &value($var->class, $context);
      // DEBUG Console::writeLine('MEMBER ', $pointer->id, '->', $var->member, ' := ', PNode::stringOf($value));
      
      $o= &$GLOBALS['objects'][$pointer->id];
      if (!($member= &member($o['name'], $var->member, $context))) {
        error(E_ERROR, 'Cannot assign to non-existant member '.$o['name'].'::'.$var->member);
      }
      
      // Check for setter
      if (NULL !== $member->args[2]) {
        switch (1) {
          case !isset($member->args[2]['set']): {
            except(createobject(new NewNode(
              new ClassReferenceNode('xp~lang~IllegalAccessException'),
              new NewClassNode(array(
                $o['name'].'->'.$var->member.' not settable'
              ))
            ), $context), $context);
            break;
          }
          
          case '$' == $member->args[2]['set']{0}: {
            $o['members'][substr($member->args[2]['set'], 1)]= $value;
            break;
          }

          default: {
            methodcall(new MethodCallNode(
              $pointer,
              $member->args[2]['set'],
              array($value),
              NULL
            ), $context);
          }
        }
      } else {
        $o['members'][$var->member]= $value;
      }
    } else if (is_a($var, 'VariableNode')) {
      // DEBUG onsole::writeLine('VAR ', $var->name, ' := ', PNode::stringOf($value));
      $context['variables'][$var->name]= $value;
    } else {
      error(E_ERROR, 'Cannot assign to '.PNode::stringOf($var));
    }
  }
  
  function except(&$throwable, &$context) {
    $context['E']= &$throwable;
  }
  
  function &method($class, $type, $name, $arguments, &$context) {
        
    // DEBUG Console::writeLine('Looking for ', $class, '::', $type, '(', $name, ') ', PNode::stringOf($arguments));
    $fallback= NULL;
    foreach ($context['classes'][$class]->statements as $decl) {
      if (!is_a($decl, $type) || ($name && $decl->name != $name)) continue;
      
      $fallback= $decl;

      // Found a possible candidate, now compare signatures
      // DEBUG Console::writeLine(' -> candidate ', PNode::stringOf($decl));
      for ($i= 0, $s= sizeof($decl->parameters); $i < $s; $i++) {
        $decltype= $decl->parameters[$i]->type;
        // DEBUG Console::writeLine('    declares arg #', $i, ' as ', $decltype);

        // Default for argument
        if ($i >= sizeof($arguments) && $decl->parameters[$i]->default !== NULL) {
          // DEBUG Console::writeLine('    argument #', $i, ' not existant, but parameter has default value ', $decl->parameters[$i]->default);
          break;
        }

        $v= value($arguments[$i], $context);
        $argtype= gettype($v);
        // DEBUG Console::writeLine('    argument #', $i, ' is ', $argtype, ': ', PNode::stringOf($v));
        
        // Compare types XXX FIXME XXX inheritance / scalar / ...
        if ($argtype != $decltype) {
          // DEBUG Console::writeLine('    *** mismatch, continuing search');
          continue 2;
        }
      }

      // Console::writeLine(' -> using declared ', PNode::stringOf($decl));
      return $decl;
    }

    // DEBUG Console::writeLine(' -> using fallback ', PNode::stringOf($fallback));
    return $fallback;
  }
  
  function callcontext(&$decl, &$arguments, &$context) {
    $callcontext= $context;
    $callcontext['variables']= array();
    for ($i= 0, $s= sizeof($decl->parameters), $a= sizeof($arguments); $i < $s; $i++) {
      if ($i >= $a) {
        $callcontext['variables'][$decl->parameters[$i]->name]= $decl->parameters[$i]->default;
      } else {
        $callcontext['variables'][$decl->parameters[$i]->name]= value($arguments[$i], $context);
      }
    }
    return $callcontext;
  }
  
  function methodcall(&$method, &$context) {
    
    // Static vs. dynamic method calls
    if (is_scalar($method->class)) {
      switch ($method->class) {
        case 'parent':            // FIXME: Will not work for static methods! FIXME: Compile-time!
          $pointer= fetchfrom($context['variables'], '$this', 'variable', $context);
          $class= $context['classes'][$GLOBALS['objects'][$pointer->id]['name']]->extends;
          $static= FALSE;
          break;
          
        default:
          $static= TRUE;
          $class= $method->class;
      }
    
    } else {
      $static= FALSE;
      $pointer= &value($method->class, $context);
      
      // Check for NPE
      if (!is_a($pointer, 'ObjectInstance')) {
        except(createobject(new NewNode(
          new ClassReferenceNode('xp~lang~NullPointerException'),
          new NewClassNode(array(
            '\''.xp::stringOf($pointer).'->'.$method->method.'\''
          ))
        ), $context), $context);
        return;
      }
      $class= $GLOBALS['objects'][$pointer->id]['name'];
    }
    // DEBUG Console::writeLine('INVOKE: ', $class.'::'.$method->method);

    if ($decl= &method($class, 'InvokeableDeclarationNode', $method->method, $method->arguments, $context)) {
      
      // We've found the method declaration, now:
      // - Build argument list
      $callcontext= callcontext($decl, $method->arguments, $context);
      
      // - Execute
      $context['__name']= $method->method;
      if (!$static) $callcontext['variables']['$this']= &$pointer;
      
      // Native methods
      if ($decl->modifiers & MODIFIER_NATIVE) {
        $return= call_user_func(
          strtr($decl->declaring, '~', '_').'_'.$decl->name, 
          $callcontext
        );
      } else {
      
        // DEBUG Console::writeLine('Executing ', $context['__name'], '::', VNode::stringOf($decl->statements));
        $return= execute($decl->statements, $callcontext);
      }
      
      // $buf->append(' ')->append('World');
      if (NULL === $context['E'] && NULL !== $method->chain) {
        foreach ($method->chain as $reference) {
          $reference->class= $return;
          $return= &value($reference, $context);

          if ($context['E']) break;
        }
      }
      
      return $return;
    }
    
    // Undefined method
    error(E_ERROR, 'Call to undefined method '.$method->toString());
  }

  function functioncall(&$function, &$context) {
    if ('parent' == $function->name) {    // FIXME: Should be done in compiler
      return methodcall(
        new MethodCallNode('parent', $context['__name'], $function->arguments, NULL), 
        $context
      );
    }
    if (!isset($context['functions'][$function->name])) {
      error(E_ERROR, 'Call to undefined function '.$function->toString());
      // bails
    }
    
    // - Execute
    $context['__name']= $function->name;
    $decl= &$context['functions'][$function->name];
    
    return execute($decl->statements, callcontext(
      $decl,
      $function->arguments,
      $context
    ));
  }

  function builtincall(&$function, &$context) {
    $arguments= array();
    for ($i= 0, $s= sizeof($function->arguments); $i < $s; $i++) {
      $arguments[]= &value($function->arguments[$i], $context);
    }
    
    return call_user_func_array($function->name, $arguments);
  }
  
  function createobject(&$object, &$context) {
    static $id= 0;

    $id++;
    $classname= $object->class->name;
    if (!isset($context['classes'][$classname])) {
      error(E_ERROR, 'Unknown class '.$classname);
    }
    
    // Handle anonymous class creation
    if ($object->instanciation->declaration) {
      $hash= memberhash($context['classes'][$classname]);
      $classname.= '$'.$id++;

      // Member inheritance
      $context['classes'][$classname]->statements= array();
      foreach (memberhash($object->instanciation->declaration) as $key => $declaration) {
        if (isset($hash[$key])) continue;   // Overwritten

        // FIXME: Set declaring class member

        $context['classes'][$classname]->statements[]= $declaration;
      }
    }

    // Register to object storage    
    $GLOBALS['objects'][$id]= array(
      'name'    => $classname,
      'members' => array()
    );
    $pointer= &new ObjectInstance($id); 
    
    // Call constructor if existant
    if ($decl= &method($classname, 'ConstructorDeclarationNode', NULL, $object->instanciation->arguments, $context)) {
      
      // Found a constructor, invoke it!
      $callcontext= callcontext($decl, $object->instanciation->arguments, $context);
      
      // - Execute, discarding return values (constructors cannot return anything!)
      $callcontext['variables']['$this']= &$pointer;
      $callcontext['__name']= '__construct';
      execute($decl->statements, $callcontext);
    }

    if ($object->instanciation->chain) {
      foreach ($object->instanciation->chain as $reference) {
        $reference->class= $pointer;
        $pointer= &value($reference, $context);

        if ($context['E']) break;
      }
    }

    // Return pointer to storage
    return $pointer;
  }
  
  function overloaded($op, &$l, &$r, &$out, &$context) {
    if (!is_a($l, 'ObjectInstance')) return FALSE;    // Short-cuircuit

    $class= $GLOBALS['objects'][$l->id]['name'];
    $args= array(&$l, &$r);
    if ($decl= &method($class, 'OperatorDeclarationNode', $op, $args, $context)) {

      // Found overloaded operator
      $callcontext= callcontext($decl, $args, $context);

      $callcontext['__name']= $op;
      $out= execute($decl->statements, $callcontext);
      return TRUE;
    }

    // Couldn't find an overloaded operator, fall through
    return FALSE;
  }
  
  function binaryop($op, &$l, &$r, &$context) {
    switch ($op) {
      case '<':
        return value($l, $context) < value($r, $context);
        break;

      case '<=':
        return value($l, $context) <= value($r, $context);
        break;

      case '>':
        return value($l, $context) > value($r, $context);
        break;

      case '>=':
        return value($l, $context) >= value($r, $context);
        break;

      case '==':
        return value($l, $context) == value($r, $context);
        break;

      case '===':
        return value($l, $context) === value($r, $context);
        break;

      case '!=':
        return value($l, $context) != value($r, $context);
        break;

      case '!==':
        return value($l, $context) !== value($r, $context);
        break;

      // Overloadable operators
      case '.':
        $left= value($l, $context);
        $right= value($r, $context);

        return overloaded('.', $left, $right, $v, $context) ? $v : $left.$right;
        break;

      case '+':
        $left= value($l, $context);
        $right= value($r, $context);

        return overloaded('+', $left, $right, $v, $context) ? $v : $left + $right;
        break;

      case '-':
        $left= value($l, $context);
        $right= value($r, $context);

        return overloaded('-', $left, $right, $v, $context) ? $v : $left - $right;
        break;

      case '*':
        $left= value($l, $context);
        $right= value($r, $context);

        return overloaded('*', $left, $right, $v, $context) ? $v : $left * $right;
        break;

      case '/':
        $left= value($l, $context);
        $right= value($r, $context);

        return overloaded('/', $left, $right, $v, $context) ? $v : $left / $right;
        break;

      case '%':
        $left= value($l, $context);
        $right= value($r, $context);

        return overloaded('%', $left, $right, $v, $context) ? $v : $left % $right;
        break;

      default:
        error(E_ERROR, 'Unsupported binary operator '.$op);
        // Bails
    }
  }
  
  function isinstance(&$pointer, $type, &$context) {
    $classname= $GLOBALS['objects'][$pointer->id]['name'];
    
    // Short-cuircuit name equality, e.g. "new Object() instanceof Object"
    if ($classname == $type) return TRUE;
    
    // Search parent classes and interfaces upwards-recursive
    do {
      $decl= &$context['classes'][$classname];
      
      if ($decl->extends == $type || in_array($type, $decl->implements)) return TRUE;
    } while ($classname= $decl->extends);
    
    return FALSE;
  }
  
  function &value(&$node, &$context) {
    if (!$context) xp::error('value() invoked outside of context');
    
    if (is_a($node, 'PNode') || is_a($node, 'VNode')) {
      $id= is_a($node, 'PNode') ? strtolower($node->type) : substr(get_class($node), 0, -4);
      
      switch ($id) {
        case 'vclassname':  // builtin
          $pointer= fetchfrom($context['variables'], '$this', 'variable', $context);
          return $GLOBALS['objects'][$pointer->id]['name'];
          break;

        case 'variable':
          return fetch($node, $context);
          break;
         
        case 'methodcall':
          return methodcall($node, $context);
          break;

        case 'new':
          return createobject($node, $context);
          break;
        
        case 'objectreference':
          $pointer= &value($node->class, $context);
          $o= &$GLOBALS['objects'][$pointer->id];
          if (!($member= &member($o['name'], $node->member, $context))) {
            error(E_ERROR, 'Cannot read non-existant member '.$o['name'].'::'.$node->member);
          }
          
          // Check for getter
          if (NULL !== $member->args[2]) {
            switch (1) {
              case !isset($member->args[2]['get']): {
                except(createobject(new NewNode(
                  new ClassReferenceNode('xp~lang~IllegalAccessException'),
                  new NewClassNode(array(
                    $o['name'].'->'.$node->member.' not gettable'
                  ))
                ), $context), $context);
                return;
              }
              
              case '$' == $member->args[2]['get']{0}: {
                return fetchfrom(
                  $o['members'], 
                  substr($member->args[2]['get'], 1), 
                  'member of '.$o['name'], 
                  $context
                );
              }
              
              default: {
                return methodcall(new MethodCallNode(
                  $pointer,
                  $member->args[2]['get'],
                  array(),
                  NULL
                ), $context);
              }
            }
          } else {
            return fetchfrom(
              $o['members'], 
              $node->member, 
              'member of '.$o['name'], 
              $context
            );
          }
          break;
        
        case 'ternary':
          return (value($node->condition, $context) 
            ? value($node->expression, $context) 
            : value($node->conditional, $context)
          );
          break;

        case 'functioncall':
          return function_exists($node->name) 
            ? builtincall($node, $context)
            : functioncall($node, $context)
          ;
          break;

        case 'binary':
          return binaryop($node->operator, $node->left, $node->right, $context);
          break;

        case 'not':
          return !value($node->expression, $context);
          break;
        
        case 'preinc':  // ++$i
          $new= value($node->expression, $context)+ 1;
          set($node->expression, $new, $context);
          return $new;
          break;

        case 'postinc': // $i++
          $new= value($node->expression, $context);
          set($node->expression, $new+ 1, $context);
          return $new;
          break;

        case 'predec':  // --$i
          $new= value($node->expression, $context)- 1;
          set($node->expression, $new, $context);
          return $new;
          break;

        case 'postdec': // $i--
          $new= value($node->expression, $context);
          set($node->expression, $new- 1, $context);
          return $new;
          break;
        
        case 'instanceof':
          return isinstance(
            value($node->object, $context), 
            $node->type->name,
            $context
          );
          break;
        
        default:
          error(E_ERROR, 'Cannot retrieve value representation of '.$node->toString());
          // Bails
      }
    } else if ('"' == $node{0}) { // Double-quoted string
      $value= '';
      for ($i= 1, $s= strlen($node)- 1; $i < $s; $i++) {
        if ('\\' == $node{$i}) {
          switch ($node{$i+ 1}) {
            case 'r': $value.= "\r"; break;
            case 'n': $value.= "\n"; break;
            case 't': $value.= "\t"; break;
          }
          $i++;
        } else {
          $value.= $node{$i};
        }
      }
      return $value;
    } else if ("'" == $node{0}) { // Single-quoted string
      return substr($node, 1, -1);
    }

    return $node;
  }


  function execute($nodes, &$context) {
    $i= 0;
    $context['offset']= &$i;
    $context['return']= 0;
    $context['end']= sizeof($nodes);

    // DEBUG Console::writeLine(PNode::stringOf($context), '>>>');
    
    for ($i= 0, $s= $context['end']; $i < $s; $i++) {
      handle($nodes[$i], $context);
      if ($context['E']) break;
    }

    // Console::writeLine('>>> returned ', xp::stringOf($context['return']));
    
    return $context['return'];
  }
  
  function handle(&$node, &$context) {
    $id= is_a($node, 'PNode') ? strtolower($node->type) : substr(get_class($node), 0, -4);

    if (!isset($context['handlers'][$id])) {
      error(E_NOTICE, 'Unknown '.$id.' node '.PNode::stringOf($node));
      return;
    }

    // echo $context['__name'], ' *** ', $node->toString(), ' ***', "\n";
    $context['handlers'][$id]->handle(
      $context, 
      $node
    );
  }
  
  // NATIVE method xp~lang~Object::getClassName()
  function xp_lang_Object_getClassName(&$context) {
    $pointer= fetchfrom($context['variables'], '$this', 'variable', $context);
    return $GLOBALS['objects'][$pointer->id]['name'];
  }

  // NATIVE method xp~lang~Object::hashCode()
  function xp_lang_Object_hashCode(&$context) {
    $pointer= fetchfrom($context['variables'], '$this', 'variable', $context);
    return $pointer->id;
  }

  // {{{ handlers
  $handlers= array();
  $handlers['assign']= &opcode('
    set($node->variable, value($node->expression, $context), $context);
  ');
  $handlers['preinc']= &opcode('
    $new= value($node->expression, $context)+ 1;
    set($node->expression, $new, $context);
  ');
  $handlers['postinc']= &opcode('
    $new= value($node->expression, $context);
    set($node->expression, $new+ 1, $context);
  ');
  $handlers['if']= &opcode('
    // if (condition) { if-statements } [ elseif { elseif-statements }] [ else { else-statements }]
    if (value($node->condition, $context)) {

      // condition: true
      $block= &$node->statements;
    } else if ($node->elseif) {

      // condition: false, else if
      if (value($node->elseif->args[0], $context)) {
        $block= &$node->elseif->args[1];
      } else {
        $block= &$node->else;
      }
    } else if ($node->else) {

      // condition: false, else
      $block= &$node->else;
    }

    if (is_array($block)) foreach ($block as $arg) {
      handle($arg, $context);
    } else handle($block, $context);
  ');
  $handlers['for']= &opcode('
    // for (init; condition; loop) { statements }
    // init
    foreach ($node->init as $arg) {
      handle($arg, $context);
    }
    
    while (value($node->condition[0], $context)) {  // condition
    
      // statements 
      foreach ($node->statements as $arg) {
        handle($arg, $context);
      }
      
      // loop
      handle($node->loop[0], $context);
    }
  ');
  $handlers['while']= &opcode('
    // while (condition) { statements }
    while (value($node->condition, $context)) {  // condition

      // statements 
      foreach ($node->statements as $arg) {
        handle($arg, $context);
      }
    }
  ');
  $handlers['dowhile']= &opcode('
    // do { statements } while (condition)
    do {

      // statements 
      foreach ($node->statements as $arg) {
        handle($arg, $context);
      }
    } while (value($node->condition, $context));
  ');
  $handlers['binaryassign']= &opcode('
    set(
      $node->variable, 
      binaryop($node->operator, $node->variable, $node->expression, $context), 
      $context
    );
  ');
  $handlers['echo']= &opcode('
    foreach ($node->args as $arg) {
      $value= value($arg, $context);
      
      if (is_scalar($value)) {
        echo $value;
      } else if (is_array($value)) {
        echo "Array";
      } else if (is_a($value, "ObjectInstance")) {
        echo "Object (".$GLOBALS["objects"][$value->id]["name"].")";
      }
    }
  ');
  $handlers['exit']= &opcode('
    except(createobject(new NewNode(
      new ClassReferenceNode(\'xp~lang~SystemExit\'),
      new NewClassNode((array)$node->expression, NULL, NULL)
    ), $context), $context);
  ');
  $handlers['return']= &opcode('
    if (isset($node->value)) {
      $context["return"]= &value($node->value, $context);
    }
    $context["offset"]= $context["end"];
  ');
  $handlers['classdeclaration']= &opcode('
    declareclass($node, $context);
  ');
  $handlers['interfacedeclaration']= &opcode('
    $context["classes"][$node->name]= $node;
  ');
  $handlers['functiondeclaration']= &opcode('
    $context["functions"][$node->name]= $node;
  ');
  $handlers['functioncall']= &opcode('
    function_exists($node->name) 
      ? builtincall($node, $context)
      : functioncall($node, $context)
    ;
  ');
  $handlers['methodcall']= &opcode('
    methodcall($node, $context);
  ');
  $handlers['try']= &opcode('
    execute($node->statements, $context);
    $oE= &$context["E"];
    $context["E"] && handle($node->catch, $context);
    
    // If Exception was not caught by `catch`, pass it to the
    // next catch (if available).
    // Do not pass a newly thrown exception to these catches (thus comparing
    // the ids).
    if (NULL !== $node->catch->catches && $context["E"] && $oE->id === $context["E"]->id) {
      execute($node->catch->catches, $context);
    }
    
    // Execute finally
    if (NULL !== $node->finally) {
      execute($node->finally->statements, $context);
    }
  ');
  $handlers['packagedeclaration']= &opcode('    // FIXME: Compile-time!
    foreach ($node->statements as $statement) { // FIXME: Only nodes with ->name and only ->name affected
      $statement->name= $node->name."~".$statement->name;
      handle($statement, $context);
    }
  ');
  $handlers['catch']= &opcode('
    if (isinstance($context["E"], $node->class, $context)) {
      $context["variables"][$node->variable]= &value($context["E"], $context);
      unset($context["E"]);
      execute($node->statements, $context);
    }
  ');
  $handlers['throw']= &opcode('
    except(value($node->value, $context), $context);
  ');
  $handlers['new']= &opcode('
    createobject($node, $context);
  ');
  // }}}
  
  // {{{ main
  $offset= 1;
  $options= array();
  while ('-' == $argv[$offset]{0} && $offset <= $argc) {
    $options[substr($argv[$offset++], 1)]= TRUE;
  }
  $nodes= unserialize(FileUtil::getContents(new File($argv[$offset])));
  
  $context= array();
  $context['E']= NULL;
  $context['__name']= '<main>';
  $context['handlers']= $handlers;
  
  // Register builtin variables
  $context['variables']= array();
  $context['variables']['$argc']= $argc- $offset;
  $context['variables']['$argv']= array_slice($argv, $offset);

  // Register builtin classes
  loadclass('xp~lang~Object', $context);
  loadclass('xp~lang~Throwable', $context);
  loadclass('xp~lang~SystemExit', $context);
  loadclass('xp~lang~Exception', $context);
  loadclass('xp~lang~NullPointerException', $context);
  loadclass('xp~lang~IllegalAccessException', $context);
  
  // Execute
  if (isset($options['t'])) {
    $timer= &new Timer();
    $timer->start();
  }
  execute($nodes, $context);
  if (isset($options['t'])) {
    $timer->stop();
  }
  
  $exitcode= 0;
  // Check for unhandled exceptions
  if ($context['E']) {
    if (isinstance($context['E'], 'xp~lang~SystemExit', $context)) {
      $o= &$GLOBALS['objects'][$context['E']->id];
      $exitcode= fetchfrom(
        $o['members'], 
        'code', 
        'member', 
        $context
      );
    } else {
      echo '*** Uncaught ', methodcall(new MethodCallNode(
        $context['E'],
        'toString',
        array(),
        NULL
      ), $context), "\n";
      $exitcode= 127;
    }
  }

  if (isset($options['t'])) {
    Console::writeLine("\n\n========================================================");
    Console::writeLinef('@VM@ Execution time= %.3f seconds', $timer->elapsedTime());
    Console::writeLinef('@VM@ Objects created: %d', sizeof($GLOBALS['objects']));
  }
  
  exit($exitcode);
  // }}}
?>
