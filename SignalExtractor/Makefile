program: program.o Neurosky.o
	g++ -g  -O3 -std=c++0x -lcurl -lbluetooth -lpthread -o program program.o Neurosky.o

Neurosky.o: Neurosky.h Neurosky.cpp
	g++ -c -g -O3 -std=c++0x Neurosky.cpp

program.o: Neurosky.h program.cpp
	g++ -c -g -O3 -std=c++0x program.cpp
