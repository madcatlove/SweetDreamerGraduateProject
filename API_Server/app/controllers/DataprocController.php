<?php


// USE PHP SOCKET.IO
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;
use App\Libraries\MQTT;
use App\Libraries\Utils;

class DataprocController extends BaseController {


	public function rawDataReceiver() {

		$data = Input::get('data');

		$client = new CLient( new Version1X('http://localhost:3636'));

		// 라인별로 데이터 분리
		$lineData = explode(",", $data);
		$lineDataSize = count($lineData);


		// 현재 요청한 시각.
		$currentTime = date("Y-m-d H:i:s", time() );

		$output = []; // 아웃풋 데이터
		for($i = 0; $i < $lineDataSize; $i++) {
			$each = $lineData[$i];
			//$each = explode(',', $lineData[$i]);
			//if( sizeof($each) !== 2 ) {
			//	continue;
			//}

			$dataType = (int)1;
			$dataRaw = (int) $each;


			// 데이터 변환 
			$convertedRaw = ($dataRaw * (1.8 / 4096.0)) / 2000.0; 
			$convertedRaw = $convertedRaw * 1000000;
			if( $convertedRaw >= -100.5 && $convertedRaw <= 100.5) {
				$output[] = $convertedRaw;

				$oRawData = new MemberRawData;
				$oRawData->rawdata = $dataRaw;
				$oRawData->regdate = $currentTime;
				//$oRawData->save();

			}
		}


		$client->initialize();
		$client->emit('neuroskyData', ['data' => $output]);
		$client->close();

		return " OKAY !";
	}



	public function rawDataReceiver2() {


		return "";
	}


	public function rawDataViewer() {

		$v = View::make('neuroskyDataViewer');


		return $v;
	}

	public function rawDumpData() {
		set_time_limit(600);

		$dumpPath = app_path() . '/dumpRawData/';

		$dateSeq = MemberRawData::groupBy('regdate')->orderBy('regdate','asc')->get();

		$dateSeq = $dateSeq->toArray();

		$len = count($dateSeq);


		// total load;
		$dumpData = MemberRawData::orderBy('regdate', 'asc')->orderBy('seq', 'asc')->get();



		// for($i = 0; $i < $len; $i++) {
		// 	$rawData = MemberRawData::where('regdate', $dateSeq[$i]['regdate'])->orderBy('seq', 'asc')->get();

		// 	$dataContent = "";
		// 	$rawData->each(function($record) use(&$dataContent) {
		// 		$convertedSignal = ($record->rawdata * (1.8 / 4096.0)) / 2000.0; 
		// 		$convertedSignal = $convertedSignal * 1000000;
		// 		$dataContent .= sprintf("%f", $convertedSignal) . PHP_EOL;
		// 	});

		// 	echo ($i+1) . ' DATA PROCESSED... '.PHP_EOL;
		// 	flush();

		// 	//echo $dataContent.PHP_EOL;

		// }


	}



	/**
	 * @brief 라즈베리파이에서 측정된 30초 윈도우 받아서 가공
	 * @details 30초 윈도우 데이터를 외부 C 프로그램으로 분류하고 결과를 DB에 저장하고 MQTT 전송
	 * @return Util
	 */
	public function userRawDataReceiver() {

		$validRule = [
			'bluetoothaddr' => array('required',
			 						 'regex:/^[0-9a-zA-Z]{2}:[0-9a-zA-Z]{2}:[0-9a-zA-Z]{2}:[0-9a-zA-Z]{2}:[0-9a-zA-Z]{2}:[0-9a-zA-Z]{2}$/')
		];

		$bluetoothaddr = array_map('trim', Input::only('bluetoothaddr') );
		$validator = Validator::make( $bluetoothaddr, $validRule);

		if( $validator->fails() ) {
			return Utils::result( Utils::CANNOT_PASS_VALIDATOR, true);
		}

		// 블루투스로 유저 조회 
		$member = Member::where('bluetoothaddr', $bluetoothaddr)->first();

		if(!isset($member) ) {
			return Utils::result( Utils::CANNOT_FIND_MEMBER);
		}

		// 외부 프로그램 동작 부분.
		$externalProgram = app_path() . '/externalProgram/hello';
		$result = '';
		$strResult = exec($externalProgram, $result, $returnVal);


		// MQTT 전송부분
		$topic = $member->userid . '/sleepStep';
		$message = ' Hello world. ' . rand();
		$mqtt = new MQTT("my.n-pure.net", 1883, "NeuroskyMQTTPUSH"); //Change client name to something unique
		if ($mqtt->connect()) {
			$mqtt->publish($topic, $message ,0);
			$mqtt->close();
		}


		return Utils::result( $strResult );
	}


}
