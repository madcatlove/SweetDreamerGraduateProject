#include <cstdio>
#include <iostream>
#include <unistd.h>
#include <sys/socket.h>
#include <bluetooth/bluetooth.h>
#include <bluetooth/rfcomm.h>
#include <string.h>
#include <vector>
#include <pthread.h>
#include <stdlib.h>
#include <time.h>
#include <string>
#include <curl/curl.h>

using std::string;

typedef unsigned char byte;
typedef struct NeuroskyData {
	byte code;
	byte length;
	int data[10];	
} NeuroskyData;


// GLOBAL FUNCTION
void* updateData(void* data);
void* updateProcessedData(void* data);


// #ifndef _NEUROSKY_HEADER_
// #define _NEUROSKY_HEADER_
// Mutex Lock
extern pthread_mutex_t tMutex;
// #endif


class Neurosky {
	
	public:
		Neurosky();
		~Neurosky();
		int init();
		
		int extractHeader();
		byte* extractPayload(int payload_length);
		int extractChecksum();
		int getChecksum(byte* payload, int payload_length);
		std::vector<NeuroskyData>* parsePayload(byte* payload, int payload_length);
		void printData(const NeuroskyData& data);
		pthread_t m_pthread_sendData;// p_thread주소를 저장하기 위한 변수
		//Data를 보내는 함수
		int start_pthread_sendData();
		void dumpData(const std::vector<NeuroskyData>& tempData); // Dump Raw Data
		void dumpProcessedData(const std::vector<NeuroskyData>& tempData); // Dump processed data


	private:
		struct sockaddr_rc bluetooth_addr; // bluetooth socket structure
		int status; // status for connection
		int sock; // int socket file descriptor
		char *destAddr;
		bool isDebug;
		byte* payload;
		std::vector<NeuroskyData>* vData;

		// PARSER
		NeuroskyData parseRawData(byte* payload);
		NeuroskyData parseEEGData(byte* payload);
		NeuroskyData parseAttention(byte* payload);
		NeuroskyData parseMeditation(byte* payload);
		NeuroskyData parsePoorSignal(byte* payload);


};
