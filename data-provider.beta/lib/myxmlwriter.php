<?php
/**
 * Own XML writer class to overcome the shortcomings of the original PHP class
 *
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.2
 */

class MyXmlWriter {
   
    var $buffer='';
    var $indent='   ';
    var $stack=array();
    
    /**
     * Initializes memory
     */
    function openMemory() {
		$this->buffer='';
		$this->stack=array();
    }
        
      /**
     * Appends memory to internal buffer.
     * XML Writer Objects 
     * @param $append XML text which should be appended  
     */
    function appendRawXML($append)
    {
       $this->addContent($append,false);
    }
    
     /**
     * Appends xml to internal buffer.
     * XML Writer Objects 
     * @param $append myxmlwriter object, which should be appended 
     */
    function appendXML($append)
    {
       $this->addContent($append->outputMemory(),false);
    }
    
    /**
     * Set indent string
     * @param $indent set the string used for XML indentation
     */
    function setIndentString($indent) {
		$this->indent=$indent;
    }
    
    /**
     * Starts a new XML document an writes header to buffer
     * @param $version optional: XML version used
     * @param $encoding optional: character encoding if differing from UTF-8
     */
    function startDocument($version="1.0", $encoding="UTF-8") {
		$this->buffer.='<?xml version="'.$version.'" ?>'."\n";
    }
    
    /**
     * Creates a new Processing instruction. Needs to be specified
     * directly after "startDocument"!! Useful for embedding stylsheets.
     * @param instructionTarget
     * @param $data optional: Sepcial information
     */
    function createProcessingInstruction($target,$data=""){
        $this->buffer.="<?".$target." ".$data."?>"."\n";
        
    }
    
    /**
     * Start a new element with a certain namespace
     * @param $prefix namespace prefix
     * @param $name element name
     * @param $nsuri namespace URI
     */
    function startElementNS($prefix,$name,$nsuri) {
        
        // Close tag if open
        if($ssize=count($this->stack)) {
            if($this->stack[$ssize-1]['o']) {
                        $this->buffer.=">".($this->indent?"\n":'');
                        $this->stack[$ssize-1]['o']=false;
            }
        }	

        // Start new tag
        // Indent
        for($i=0;$i<$ssize;$i++) $this->buffer.=$this->indent;

        $this->buffer.='<';

        // Add prefix if missing
        if($prefix!==NULL) $this->buffer.=$prefix.':';

        $this->buffer.=$name;

        // Add namespace declaration if missing
        if($nsuri!==NULL && (!$ssize || ($this->stack[$ssize-1]['u']!=$nsuri)))
            $this->buffer.=' xmlns'.(($prefix!==NULL)?(':'.$prefix):'').'="'.$nsuri.'"';

        $this->stack[$ssize]=array(
                'o'=>true,
                'n'=>$name,
                'u'=>$nsuri,
                'p'=>$prefix,
                't'=>false
        );
    }
    
    /**
     * Add text to the current element
     * @param $text text to add
     * @param $inputShouldBeNormalized optional: If xml or special code fragments should be
     *        appended, set to false
     */
    function addContent($text,$inputShouldBeNormalized = true) {
        
		if($this->stack[($ssize=count($this->stack))-1]['o']) {
		    $this->buffer.='>';
		    $this->stack[$ssize-1]['o']=false;
		}
		$this->stack[$ssize-1]['t']=true;
                
                if($inputShouldBeNormalized){
                    $this->buffer.=str_replace(array('&','<','>'),array('&amp;','&lt;','&gt;'),$text);
                }else{
                    $this->buffer.=$text;
                }
    }
	
    /**
     * Closes the current element
     */
    function endElement() {
        if($ssize=count($this->stack)) {
		    $e=$this->stack[$ssize-1];
		    if($e['o']) {
				$this->buffer.="/>".($this->indent?"\n":'');
		    } else {
				if(!$e['t']) {
				    if($this->indent) {
						if(substr($this->buffer,-1)!="\n")
						    $this->buffer.="\n";
						for($i=1;$i<$ssize;$i++)
						    $this->buffer.=$this->indent;
				    }
				}
				$this->buffer.='</'.(($e['p']!==NULL)?($e['p'].':'):'').$e['n'].">".($this->indent?"\n":'');
		   	}
                        unset($this->stack[$ssize-1]);
		}
    }

    /**
     * Starts a new element, adds content and closes it
     * @param $prefix namespace prefix
     * @param $name element name
     * @param $nsuri namespace URI
     * @param $content text to add
     */
    function writeElementNS($prefix,$name,$nsuri,$content,$replace = true) {
        $this->startElementNS($prefix,$name,$nsuri);
		$this->addContent($content,$replace);
		$this->endElement();
    }

    /**
     * Starts a new element
     * @param $name element name
     */
    function startElement($name) {
	$this->startElementNS(NULL,$name,NULL);
    }

    /**
     * Adds a attribute and its value considering namespace settings
     * @param $prefix namespace prefix
     * @param $attr attribute name
     * @param $nsuri namespace URI
     * @param $value attribute value
     */
    function writeAttributeNS($prefix,$attr,$nsuri,$value) {
		if($this->stack[($ssize=count($this->stack))-1]['o']) {
		    if(($nsuri!==NULL) && ($prefix!==NULL)) {
				if(($nsuri!=$this->stack[$ssize-1]['u']) || ($prefix!=$this->stack[$ssize-1]['p']))
			    $this->buffer.=' xmlns:'.$prefix.'="'.$nsuri.'"';
				$this->buffer.=' '.$prefix.':'.$attr.'="'.$value.'"';
		    } else {
				$this->buffer.=' '.$attr.'="'.$value.'"';
		    }
		}
    }

    /**
     * Adds an attribute and value
     * @param $attr attribute name
     * @param $value attribute value
     */
    function writeAttribute($attr,$value) {
		$this->writeAttributeNS(NULL,$attr,NULL,$value);
    }

    /**
     * Returns the current output buffer
     * @param $flush set true to flush the buffer
     * @return string contents of the output buffer
     */
    function outputMemory($flush=true) {
	    $ret=$this->buffer;
	    if($flush)
		$this->buffer='';
	    return $ret;
    }
}
