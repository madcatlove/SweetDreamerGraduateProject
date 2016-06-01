#include <iostream>
#include <cstdio>
#include <unistd.h>
#include <signal.h>
#include "Neurosky.h"

using namespace std;

//---------------------
Neurosky *ns;
int status;
//---------------------

void alarmHandler(int signo) {
	alarm(0);
	printf(" >>>>>> RESTART NEUROSKY (signo:%d) <<<<< \n", signo);
	
	status = 0;
	int retry = 1;

	do {
		cout << "Try to re-connect Neurosky :: " << retry << endl;
		status = ns->init();
		retry++;
	} while(!status);
	

	// release alarm
	cout << " >>>>>> Restart successfully (release alarm) " << endl;
	alarm(0);

}


int main() {
	
	cout << " >>>> START NEUROSKY <<<<< " << endl;
	cout << endl;

	//Neurosky* ns = new Neurosky();
	ns = new Neurosky();
	
	// register SIGALRM
	signal( SIGALRM, alarmHandler );

	status = 0;

	int retry = 1;
	do {
		cout << " Try to connect Neurosky :: " << retry << endl;
		status = ns->init();
		retry++;
	} while( !status );
	

	int payload_length = 0;
	byte* payload = NULL;
	int checksum = 0;
	std::vector<NeuroskyData>* data = NULL;
	

	//--------------------------
	// For temporary dump
	//--------------------------
	std::vector<NeuroskyData> tempData;
	std::vector<NeuroskyData> tempProcessedData;

	while(1) {
		// set alarm
		alarm(1);
		
		// extract header
		payload_length = ns->extractHeader();

		if( payload_length < 0 ) {
			// printf(" Corrupt header (Length : %d) \n", payload_length);
			continue;
		}

		// extract payload
		payload = ns->extractPayload( payload_length );

		if( payload == NULL) {
			printf(" Cannot extract payload \n");
			continue;
		} 

		// getChecksum
		checksum = ns->getChecksum( payload, payload_length );
		int dataChecksum = ns->extractChecksum();
		if( checksum != dataChecksum ) {
			// printf(" Checksum is corrupt (DataChecksum :%d, Getting:%d) \n",
			// 				checksum,
			//				dataChecksum);
			continue;
		}

		data = ns->parsePayload(payload, payload_length);
		for(int i = 0; i < data->size(); i++) {
			NeuroskyData sData = data->at(i);
			ns->printData( sData );
			if( sData.code == 0x80 ) {
				tempData.push_back( sData );
			}
			else if( sData.code == 0x83 ) {
				//printf(">>>>>>>>> PROCESSED DATA PUSHED !!! \n");
				//tempProcessedData.push_back( sData );
			}
			
		}


		// DUMP RAW DATA
		if( tempData.size() >= 512 ) {
			printf(">>>>>>>>> START DUMP DATA (RAW DATA) <<<<<<<<<<<\n");
			ns->dumpData( tempData );
			tempData.clear();
		}

#if 0
		if( tempProcessedData.size() >= 30) {
			ns->dumpProcessedData( tempProcessedData );
			tempProcessedData.clear();
		}
#endif
		

		// release alarm
		alarm(0);
		
		// unallocate data
		//for(int i = 0; i < data->size(); i++) {
		//	delete[] (data->at(i)).data;
		//}
	}

	// release
	delete ns;
	return 0;
}
