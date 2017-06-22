<?php

App::uses('AppController', 'Controller');

class ExampleController extends AppController {
	
	public function index() {
		if($this->request->is('post') && isset($this->request->data['text']) && ! empty($this->request->data['text'])){
			$this->loadModel('PresentationExample');
			$filename = $this->PresentationExample->GeneratePresentation($this->request->data['text']);
			if (!empty($filename)) {
			    $this->downloadFile($filename);
      		      }
		}
	}
	public function sendmail() { 
	   if(isset($this->request->data['file'])){
		 $userfile=$this->request->data['file'];
		// $uploaddir = APP . 'webroot/retailerdocument/';
                 $docpath=array();
		 /*foreach($userfile as $files){
			/*if (move_uploaded_file($files['tmp_name'], $uploaddir .$files['name'])) {
                            $docpath[$files['name']] = array('data' => WWW_ROOT.'retailerdocument' . DS .$files['name']);
                        }else {
                             pr('unable to upload file ');
                        }

                 } */               
                 // $fileData = fread(fopen($userfile['tmp_name'], "r"), $userfile['size']); for save in database
                 //if (file_exists($uploaddir . $uploadfile) || is_uploaded_file($uploaddir . $uploadfile)) {
                   // pr('File Already Exists');
                  //}
		foreach($userfile as $files){
			$file = fopen($files['tmp_name'],"r");
                        $docpath[$files['name']] = array('data' => fread($file,$files['size']),'mimetype'=>$files['type']);
			fclose($file);
                 }   				
		App::uses('CakeEmail', 'Network/Email');
		$Email = new CakeEmail();
		$Email->from(array('chetansharma7737@gmail.com' => 'My Site'))
  		      ->to('chetan.sharma@sugaldamani.com')
  		      ->subject('About')
		      ->attachments($docpath)
   		      ->send('My message');
                      
	   }

	}
       public function genratePdf(){


       }
       
       public function genrateExcel(){
	App::import('Vendor', 'excel/PwExcel');
        $phpExcel=new PwExcel();
        $data[0]['name']="chetan1";
	$data[1]['name']="chetan1";
	$data[2]['name']="chetan1";
	$data[3]['name']="chetan1";
	$data[4]['name']="chetan1";
	$data[5]['name']="chetan1";
        $filename=$phpExcel->generate($data);
	$this->downloadFile($filename);
       }
       function downloadFile($filename){
       	$this->response->download($filename);
        $fl = fopen(TMP . $filename, 'r');
	$this->set('filedata', fread($fl, filesize(TMP . $filename)));
	fclose($fl);
	unlink(TMP . $filename);
	$this->render('/Elements/files');
       } 
}
