VERSION=0.0.1

test:
	nittest -cp build cc.arduino.unittest.**

release: test
	mkdir -p dist ; cd build && xar cvf ../dist/arduino.$(VERSION).xar .

clean:
	rm -rvf build/*
