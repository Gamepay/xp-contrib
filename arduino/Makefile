VERSION=0.0.1

compile:
	xcc -o build -sp src src/

test: compile
	unittest -cp build cc.arduino.unittest.**

release: test
	mkdir -p dist ; cd build && xar cvf ../dist/arduino-api.$(VERSION).xar .

clean:
	rm -rvf build/*

