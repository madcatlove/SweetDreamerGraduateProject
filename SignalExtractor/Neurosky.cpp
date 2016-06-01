#include "Neurosky.h"
//#include "Neurosky_sendingData.h"
#include <errno.h>

#define BLUETOOTH_DEST_ADDR "74:E5:43:9C:5E:4C"
#define _PAYLOAD_LENGTH_ 256
#define ISDEBUG true

pthread_mutex_t tMutex;

/**
 * Constructor for neurosky
 */
Neurosky::Neurosky() {
		destAddr = new char[ strlen(BLUETOOTH_DEST_ADDR) + 1];
		strcpy( destAddr, BLUETOOTH_DEST_ADDR );

		// allocate payload
		payload = new byte[ _PAYLOAD_LENGTH_ ];

		// allocate data vector
		vData = new std::vector<NeuroskyData>();

		isDebug = ISDEBUG;
		tMutex = PTHREAD_MUTEX_INITIALIZER;

		//데이터 전송을 위한 벡터 초깃화
		//neuroskyData_Frequency = new std::vector<NeuroskyData>();
		//neuroskyData_RawData = new std::vector<NeuroskyData()>;
	
		sock = -1;
}

/**
 * Destructor for neurosky
 */
Neurosky::~Neurosky() {
		if( destAddr != NULL) delete destAddr;
		if( payload != NULL) delete[] payload;
		if( vData != NULL) delete vData;
	//	if( neuroskyData_Frequency!=NULL) delete neuroskyData_Frequency;
	//	if(neuroskyData_RawData!=NULL) delete neuroskyData_RawData;

}


/**
 * It initialize bluetooth socket and connect to it
 * return 1 ( success ) 0 fail
 */
int Neurosky::init() {
		
		struct timeval timeout;
		timeout.tv_sec = 5;
		timeout.tv_usec = 0;

		// check
		if( sock != -1) close(sock);


		// Allocate socket
		sock = socket( AF_BLUETOOTH, SOCK_STREAM, BTPROTO_RFCOMM );
		setsockopt( sock, SOL_SOCKET, SO_RCVTIMEO, (char *)&timeout, sizeof(timeout));


		// connection param (init bluetooth_addr struct (sockaddr_rc) )
		bluetooth_addr.rc_family = AF_BLUETOOTH;
		bluetooth_addr.rc_channel = (byte) 1;
		str2ba( destAddr , &(bluetooth_addr.rc_bdaddr) );

		// connect to bluetooth device
		status = connect( sock,
						(struct sockaddr *) &bluetooth_addr,
						sizeof(bluetooth_addr)
						);

	  printf("%s\n", strerror(errno));

		return (status == 0) ? 1 : 0;
}

int Neurosky::extractHeader() {
		const int maskSync = 0xAA;
		unsigned char header[3] = {0, };
		int c;
		//printf(" -- Extract Header --\n");

		// read header
		c = read( sock, header, sizeof(header));

		//for(int i = 0; i < 4; i++) {
		//	printf("%u ", (unsigned int) header[i]);
		//}


		if( maskSync == header[0] && header[0] == header[1]) {
				// return plength;
				// printf("\t Extract success : LENGTH_OF_PAYLOAD : %d\n", (int) header[2]);
				return (int) header[2];
		}

		return -1;


}

byte* Neurosky::extractPayload(int payload_length) {
		if( payload_length >= 170 ) {
				printf(" Length of payload is too long !! \n");
				return NULL;
		}

		int c;	
		c = read( sock, payload, payload_length * sizeof(byte) );
		return (byte *) payload;
}

int Neurosky::getChecksum(byte* payload, int payload_length) {
		int checksum = 0;

		for(int i = 0; i < payload_length; i++) checksum += payload[i];
		checksum = checksum & 0xFF;
		checksum = ~checksum & 0xFF;

		return checksum;
}


int Neurosky::extractChecksum() {
		byte checksum = 0;
		int c;

		c = read( sock, &checksum, sizeof(byte));

		return checksum;
}

std::vector<NeuroskyData>* Neurosky::parsePayload(byte* payload, int payload_length) {
		int parsedByte = 0;
		byte exCode;
		byte code;
		byte length;

		const byte _EXCODE_ = 0x55;
		const byte _CODE_LENGTH_ = 0x80;
		NeuroskyData sData;
		std::vector<NeuroskyData>* nVector = vData;


		if( nVector->size() > 0 ) nVector->clear(); // clear vector data

		//--------------------------
		// PAYLOAD
		// EXCODE - CODE - LENGTH - DATA
		//--------------------------

		while( parsedByte < payload_length) {


				// -- GET EXCODE --
				exCode = (byte) 0;
				while( payload[parsedByte] == _EXCODE_ ) {
						exCode++;
						parsedByte++;
				}

				// -- GET CODE --
				code = payload[ parsedByte++ ];
				if( code & _CODE_LENGTH_ ) {
						length = payload[parsedByte++];
				}
				else {
						length = 1;
				}

				// -- GET DATA --
				// printf(" EXCODE : %d, CODE :  0x%02X, Length : %d\n",
				//				exCode, code, length);
				// printf("\t Data Value : \n");

				switch( code ) {
						case 0x02:
								if(isDebug) {
										printf("0x02 ==> POOR_SIGNAL \n");
								}
								sData = this->parsePoorSignal(payload + parsedByte);
								break;

						case 0x04:
								if(isDebug) {
										printf("0x04 Attention level \n");
								}
								sData= this->parseAttention(payload + parsedByte);
								break;

						case 0x05:
								if(isDebug) {
										printf("0x05 Meditation level \n");
								}
								sData = this->parseMeditation(payload + parsedByte);
								break;

						case 0x80:
								if(isDebug) {
										printf("0x80 16bit raw data \n");	
								}

								sData = this->parseRawData(payload + parsedByte);
								//neuroskyData_RawData에 sData를 집어넣음
								//neuroskyData_RawData.push_back(sData);	
								break;

						case 0x83:
								if(isDebug) {
										printf("0x83 Several attributes of EEG_POWER \n");
								}
								sData = this->parseEEGData(payload + parsedByte);
								//neuroskyData_Frequency.push_back(sData);	
								break;

						case 0x03:
								if(isDebug) {
										printf("0x03 HEART_RATE \n");
								}
								break;

						default:
								if(isDebug) {
										printf("Code : 0x%02X \n", code);
								}
				}

				//sData = callback(payload); // --; member function cannot pass to function pointer
				nVector->push_back(sData);

				//for(int i = 0; i < length; i++) {
				//	byte data = payload[parsedByte + i] & 0xFF;
				//	printf("\t   DATA :: %02X \n", data);
				//}

				parsedByte += length;
		}

		return nVector;
}

/*
   void Neurosky::destroyData(const byte* payload, const struct NeuroskyData& data) {

   if( payload != NULL) {
   delete[] payload;
   }

   if( data.data != NULL) {
   delete[] data.data;
   }
   }
 */


void Neurosky::printData(const NeuroskyData& data) {

		// if( data.code != 0x83 ) return;

		switch( data.code ) {
				case 0x02:
						// printf("\t >> PoorSignal : %u\n", (byte)(data.data[0] & 0xFF) );
						break;

				case 0x04:
						// printf("\t >> AttentionLevel : %u\n", (byte) (data.data[0] & 0xFF) );
						break;

				case 0x05:
						// printf("\t >> MeditationLevel : %u\n", (byte) (data.data[0] & 0xFF));
						break;

				case 0x80:
						printf("\t >> Raw Wave Value(2byte) :%d\n", (signed short)(data.data[0] & 0xFFFF) );
						printf("%d\n", (signed short) ( data.data[0] & 0xFFFF) );
						break;

				case 0x83:
						
						const unsigned int threeMask = 0x00FFFFFF;
						unsigned int delta = (unsigned int)data.data[0] & threeMask;
						unsigned int theta = (unsigned int)data.data[1] & threeMask;
						unsigned int lowAlpha = (unsigned int)data.data[2] & threeMask;
						unsigned int highAlpha = (unsigned int)data.data[3] & threeMask;
						unsigned int lowBeta = (unsigned int)data.data[4] & threeMask;
						unsigned int highBeta = (unsigned int)data.data[5] & threeMask;
						unsigned int lowGamma = (unsigned int)data.data[6] & threeMask;
						unsigned int midGamma = (unsigned int)data.data[7] & threeMask;

						printf("\t >> Delta : %u \n", 		delta);
						printf("\t >> Theta : %u \n", 		theta);
						printf("\t >> Low-alpha : %u \n",  	lowAlpha);
						printf("\t >> High-alpha : %u \n",  highAlpha);
						printf("\t >> Low-beta : %u \n",  	lowBeta);
						printf("\t >> High-beta : %u \n",  	highBeta);
						printf("\t >> Low-gamma : %u \n",  	lowGamma);
						printf("\t >> Mid-gamma : %u \n",  	midGamma);
						break;

		}
}

void Neurosky::dumpData(const std::vector<NeuroskyData>& tempData) {	
		printf(" Dumpdata : size => %d \n", tempData.size());

		char buff[1000] = {0,};
		string* tData = new string();

		*tData += "";
		for(int i = 0; i < tempData.size(); i++) {
				sprintf(buff, "%d,%d\n", tempData[i].code, (signed short)( tempData[i].data[0] & 0xFFFF));
				(*tData) += buff;
		}

		pthread_t sThread;
		int threadResult;
		
		printf(">>>>>> START DUMP DATA THREAD( RAW DATA POSTING ) \n");
		threadResult = pthread_create(&sThread, NULL, updateData, tData);
		// detach
		pthread_detach( sThread );

		//int status;
		//pthread_join(sThread, (void**)&status);
}

void Neurosky::dumpProcessedData(const std::vector<NeuroskyData>& tempData) {
		printf(" Dump processed data SIZE : %d \n", tempData.size() );

		char buff[1000] = {0,};
		string* tData = new string();

		*tData += "";

		//--- Processing --
		const unsigned int mask = 0x00FFFFFF;
		for(int i = 0; i < tempData.size(); i++) {
				NeuroskyData d = tempData.at(i);
				sprintf(buff, "%d,%u,%u,%u,%u,%u,%u,%u,%u\n",
								d.code,
								(unsigned int)d.data[0] & mask,
								(unsigned int)d.data[1] & mask,
								(unsigned int)d.data[2] & mask,
								(unsigned int)d.data[3] & mask,
								(unsigned int)d.data[4] & mask,
								(unsigned int)d.data[5] & mask,
								(unsigned int)d.data[6] & mask,
								(unsigned int)d.data[7] & mask
					   );

				(*tData) += buff;
		}

		//---- thread --
		pthread_t sThread;
		int threadResult;
	
		printf(">>>>> START DUMP PROCESSED THREAD \n");
		threadResult = pthread_create(&sThread, NULL, updateProcessedData, tData);
		//detach
		pthread_detach( sThread );
}


void* updateProcessedData(void *data) {
		pthread_t tid = pthread_self();
		printf(" Processed Data Dump THread is Running... (%u (0x%x)) ...",
						(unsigned int) tid,
						(unsigned int) tid
			  );

		const char reqUrl[] = "http://laravel.my.n-pure.net/app/public/index.php/data/proc";

		CURL *curl;
		CURLcode res;

		string *t  = (string *) data;
		string finalData;
		finalData = "data=";
		finalData += t->c_str();


		curl = curl_easy_init();
		if(curl) {
				curl_easy_setopt(curl, CURLOPT_URL, reqUrl);
				curl_easy_setopt(curl, CURLOPT_WRITEDATA, stdout);
				curl_easy_setopt(curl, CURLOPT_POSTFIELDS, finalData.c_str());

				res = curl_easy_perform(curl);

				if( res == CURLE_OK ) {
						printf(" DATA_TRANSFER(PROCESSED) OK \n");
				}
		}
		curl_easy_cleanup(curl);

		if( t != NULL) delete t;

		return NULL;
}



void* updateData(void *data) {
		pthread_t tid = pthread_self();
		printf(" Thread is running (id : %u(%0x%x)) ..... "
						,(unsigned int) tid
						,(unsigned int) tid);

		const char reqUrl[] = "http://laravel.my.n-pure.net/app/public/index.php/data/proc";
		char destAddr[20];
		strcpy(destAddr, BLUETOOTH_DEST_ADDR);


	  	//----------------------------
		// READ FILE
		//----------------------------
		FILE* fp = fopen("./upload/list.txt", "r");
		char _filename[50] = {0,};
		string s_filename = "./upload/";
		char noisePeak[15] = {0,};
		bool bReadFile = false;
		if( fp != NULL) {
			bReadFile = true;
			fscanf(fp, "%s %s", _filename, &noisePeak);
			s_filename = s_filename + _filename;
		}



		CURL *curl;
		CURLcode res;
		
		struct curl_httppost* post = NULL;
		struct curl_httppost* last = NULL;

		string *t = (string *) data;
		string finalData;
		finalData  = "data=";
		finalData += t->c_str();

		pthread_mutex_lock(&tMutex); //LOCK

		
		curl = curl_easy_init();
		if( curl ) {
			//----------------------------------------------------------
			// ADD POST FIELD
			if( bReadFile == true) {
				curl_formadd(&post, &last,
												CURLFORM_COPYNAME, "noisefile",
												CURLFORM_FILE, s_filename.c_str(),
												CURLFORM_END);

				curl_formadd(&post, &last,
												CURLFORM_COPYNAME, "noisepeak",
												CURLFORM_COPYCONTENTS, noisePeak,
												CURLFORM_END);
			}

			curl_formadd(&post, &last,
											CURLFORM_COPYNAME, "data",
											CURLFORM_COPYCONTENTS, finalData.c_str(),
											CURLFORM_END);

			curl_formadd(&post, &last,
											CURLFORM_COPYNAME, "bluetoothaddr",
											CURLFORM_COPYCONTENTS, destAddr,
											CURLFORM_END);
			//---------------------------------------------------------			
						
			curl_easy_setopt(curl, CURLOPT_URL, reqUrl);
			curl_easy_setopt(curl, CURLOPT_WRITEDATA, stdout);
			//curl_easy_setopt(curl, CURLOPT_POSTFIELDS, finalData.c_str() );
			curl_easy_setopt(curl, CURLOPT_HTTPPOST, post);
			curl_easy_setopt(curl, CURLOPT_NOSIGNAL, 1L);
			curl_easy_setopt(curl, CURLOPT_TIMEOUT, 10L);

			printf(" ==> PERFORM_CURL(%s, %s) \n", s_filename.c_str(), noisePeak);
			res = curl_easy_perform(curl);

			if( res == CURLE_OK ) {
					printf(" DATA_TRANSFER(RAWDATA) OK \n");
			} else {
			    printf(" DATA_TRANSFER FAILED (%s)\n", curl_easy_strerror(res));
			}
		}
		if( curl ) {
			curl_easy_cleanup(curl);
			curl_formfree(post);
		}

		if( t != NULL) delete t;

		pthread_mutex_unlock(&tMutex); // Unlock

		return NULL;
}


//----------------------------------------
// PARSER
//----------------------------------------
NeuroskyData Neurosky::parseRawData(byte* payload) {
		NeuroskyData data;

		data.code = 0x80;
		//data.data = new int[1];
		data.length = 1;

		int raw = payload[0] * 256 + payload[1];
		if( raw >= 32768 ) raw = raw - 65536;
		// printf(" Raw wave.... :%d \n", raw);


		int& r = data.data[0];

		// r = (short) (( payload[0] << 8) | payload[1]);
		r = raw;

		return data;
}

NeuroskyData Neurosky::parseEEGData(byte* payload) {
		const int payloadSize = 24;

		NeuroskyData data;
		data.code = 0x83;
		// data.data = new int[8];
		data.length = 8;

		for(int i = 0; i < data.length; i++) {
				int& r = data.data[i];
				int payloadOffset = (i * 3 );

				/*
				   r = (r | payload[payloadOffset + 0]) << 8;
				   r = (r | payload[payloadOffset + 1]) << 8;
				   r = (r | payload[payloadOffset + 2]); */

				r = ( (payload[payloadOffset] << 16) | (payload[payloadOffset+1] << 8) | (payload[payloadOffset+2]) );
		}

		return data;
}

NeuroskyData Neurosky::parseAttention(byte* payload) {
		NeuroskyData data;
		data.code = 0x04;
		// data.data = new int[1];
		data.length = 1;

		int& r = data.data[0];
		r = (byte) (payload[0] & 0xFF);

		return data;
}


NeuroskyData Neurosky::parseMeditation(byte* payload) {
		NeuroskyData data;
		data.code = 0x05;
		// data.data = new int[1];
		data.length = 1;
		int& r = data.data[0];
		r = (byte) ( payload[0] & 0xFF);

		return data;
}

NeuroskyData Neurosky::parsePoorSignal(byte* payload) {
		NeuroskyData data;
		data.code = 0x02;
		// data.data = new int[1];
		data.length = 1;


		int& r = data.data[0];
		r = (byte) (payload[0] & 0xFF);

		return data;
}



