<?php
class PwDownload
{
	Public Static function DownloadFile(&$DataObj, $filename){
		//$filename=$this->array_merge['filename'];
		$path_parts = pathinfo(APP.DS.'tmp'.DS.$filename);
		$DataObj->viewClass="Media";
        $params=array('id'=>$filename,
                      'name'=>$path_parts['filename'],
                      'extention'=>$path_parts['extension'],
                      'download'=>true,
					  'compress'=>true,
					  'unlinkfile'=>true,
                      'path'=>APP.DS.'tmp'.DS);
        $DataObj->set($params);
	}
/*	Public Static function DownloadFile(&$DataObj, $filename){
	  header('Content-Description: File Transfer');
          header("Content-type: application/vnd.ms-excel");
          header('Content-Disposition: attachment;filename="'.$filename.'"',true);
          header('Cache-Control: max-age=0');
          readfile(APP.DS.'tmp'.DS.$filename);
          unlink(APP.DS.'tmp'.DS.$filename);
	}*/
	Public Static Function DownloadJad(&$DataObj, $filename){
        $DataObj->viewClass="Media";
        $params=array('id'=>'payworld.jad',
                      'name'=>'payworld',
                      'extention'=>'jad',
                      'download'=>true,
                      'mimeType'=>array('jad'=>'text/vnd.sun.j2me.app-descriptor'),
                      'path'=>'MobileApps'.DS.'JavaApps'.DS.'tmp'.DS);
        $DataObj->set($params);
    }
    Public Static Function DownloadJar(&$DataObj, $filename){
        $DataObj->viewClass="Media";
        $params=array('id'=>'payworld.jar',
                      'name'=>'payworld',
                      'extention'=>'jar',
                      'download'=>true,
                      'mimeType'=>array('jar'=>'application/java-archive'),
                      'path'=>'MobileApps'.DS.'JavaApps'.DS.'tmp'.DS);
        $DataObj->set($params);
    }
}
