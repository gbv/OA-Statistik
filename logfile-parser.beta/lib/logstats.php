<?php

/**
 * Logstats
 * 
 * Creates simple statistics for the logfileparser
 * 
 * @author Marc Giesmann <giesmann@sub.uni-goettingen.de> for SUB Göttingen
 * @edited by Marc Giesmann
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.3.4
 */

 class LogStats{
     var $stats = array();
     
     function addStat($messagetype, $topic, $addValue=0){
         if(!isset($this->stats[$messagetype][$topic]))
            $this->stats[$messagetype][$topic] = 0;

         
         if($addValue==0)
             $addValue = 1;
         
         $this->stats[$messagetype][$topic]+=$addValue;
     }
     
     
     function combineByMessagetype($messageType){
         $return = 0;
         foreach($this->stats[$messageType] as $topic)
             $return+=$topic;
         
         return $return;
     }
     
     function combineStat($logstats){
         foreach($logstats->stats as $messagetype => $topics)
         {
                foreach($topics as $topic => $value){
                    $this->addStat($messagetype, $topic, $value);
                }
         }
     }
     
     function getCompleteStats(){
         $message = "\n ------------ STATS ------------ \n";
         
         foreach($this->stats as $messagetype => $topics)
         {
             $message.= $messagetype ."(".$this->combineByMessagetype($messagetype).")". "\n";
             
                foreach($topics as $topic => $value){
                    if($topic !=='')
                        $message.="\t-> ".$topic."\t : \t".$value."\n";
                
                }
             $message.="______________________\n\n";
         }
         
         return $message;
     }
     
     
     
     
 }//class

?>
