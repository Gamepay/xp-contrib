/**
 * This file is part of the XP framework
 *
 * $Id: StaticContextFactory.java 11854 2008-03-22 14:14:59Z kiesel $
 */
package net.xp_framework.easc.server.standalone;

import java.util.Hashtable;
import javax.naming.Context;
import javax.naming.spi.InitialContextFactory;

/**
 * Static context factory used for naming
 * 
 */
public class StaticContextFactory implements InitialContextFactory {

    /**
     * Creates an Initial Context for beginning name resolution.
     * 
     * @access  public
     * @param   java.util.Hashtable<?, ?> environment
     * @return  javax.naming.Context
     */
    public Context getInitialContext(Hashtable<?, ?> environment) {
        return new StaticContext();
    }

}
