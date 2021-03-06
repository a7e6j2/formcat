<?
/**
 * Project: Formcat, form client-side validate class.
 * File:    formcat.class.php
 * 
 * (c) 2004-2005 Justto.com Propagator Team
 * Author: Joey Wong [joey@justto.com][MSN:gzjoey@hotmail.com]
 * Website: http://formcat.justto.com
 * Released under both BSD and GNU Lesser GPL library license. 
 * This means you can use it in proprietary products.
 *
 * For questions, help, comments, discussion, etc.,please contact
 * joey@justto.com
 * 
 * The latest version of Formcat can be obtained from:
 * http://formcat.justto.com/ or http://formcat.joey.cn
 *
 * @link http://formcat.justto.com/ or http://formcat.joey.cn
 * @copyright 2004,2005 Joey Wong.
 * @author Joey <joey@163.com> [msn:gzjoey@hotmail.com]
 **/
class formcat{


	var $validationList;                                               //validation list
	var $formList;                                                     //form list
	var $defaultErrMsg = "%s is invalid,Please check again";           //Default error message ,when message attribute is empty,it will be replacement.
	var $customJs = array();                                           //Customizing Javascript.
	var $validatorPath = "validators/";                                //Validators path (pro version only)
	var $version = "1.0.1 Pro";                                        //Version 
	
  /**
   * Constructor for the class
   * sets the data source, initializes the data container
   * @param string $form formname which in validation
   **/	
   function formcat(){
		$this->formList = &$_SESSION['catformList'];
		
	}
	
  /**
   * formcat output fitler
   * Performs the generation of Javascript and replace the source of smarty output
   * 
   * @param string $source Source being filtered
   * @param Smarty $smarty Smarty object filtering the content
   * @return string filtered source 
   **/	
	function fcOutputFilter($source,&$smarty){
		
		$this->genJsCode();
		
		$replace = "<script language=Javascript type=text/javascript>".
		          "\n//--------------------------------------------------------------------------//\n".
		           "//This javascript is generated by PIGCAT FREAMWORK(FORMCAT PLUGIN ".$this->version.")\n".
		           "//Author: Joey@justto.com(MSN:gzjoey@hotmail.com)\n".
		           "//Special Thanks: Charlotte Chan		          \n".
		           "//2005 COPYRIGHT BY JUSTTO INC. DO NOT REMOVE THIS LICENSE NOTES!\n".
		           "//--------------------------------------------------------------------------//\n".
  					$this->jsStr.
		           "</script>\n";	  
		
		//place the javascript to html		                    
		if(preg_match('/<(form|FORM|Form)[^>]*>/',$source, $regs)){
			$formTags = $regs[0];
			$source = str_replace($formTags,$replace.$formTags,$source);
		}
		
		return $source;
	}
	
  /**
   * Javascript function header generator
   * generate the Javascript function header
   * 
   * @return string JS function header
   **/	
	function genJsFuncHeader($formName){
		$str = "function pigcatFCValidate_".$formName."(fc){\n";
		return $str;
	}
	
  /**
   * Javascript function footer generator
   * generate the Javascript function footer
   * 
   * @return string JS function footer
   **/	
	function genJsFuncFooter(){
		$str = "\n\n}\n\n";
	    return $str;
	}
	
  /**
   * Javascript function body generator
   * generate the Javascript function body
   * 
   * @return string JS function body
   **/	
	function genJsCode(){
	
		foreach($this->formList as $formKey=>$formName){
			
			$validationList = $_SESSION['catform_'.$formName];
	
			//generate the validation javascript
		    foreach($validationList as $fcKey=>$fcValidation){
		    	$validator = "js_validator_".$fcValidation['check'];
				//include the validator php
				$validatorFile = $this->validatorPath.$validator.".php";
				if(file_exists($validatorFile)){
					
					require_once $validatorFile;
					
				}		    	
				// check if the function is defined in the included file
		    	if (!function_exists($validator)) {
              		trigger_error("[FORMCAT WARNING]Validation function $validator not found in $validatorFile");
            	
            	}else{
		    		
		    		$genStr = $validator(array_change_key_case($fcValidation));
		    		$jsStr.=$genStr;
		    	}
		    }
		    
		    $this->jsStr.= $this->genJsFuncHeader($formName).$jsStr;
		    if($this->customJs[$formName]!='')  $this->jsStr.= $this->customJs[$formName];
		   
		     $this->jsStr.= $this->genJsFuncFooter();
			
			//clear the session and js string for next round
			session_unregister('catform_'.$formName);
			$jsStr = '';
		}
		session_unregister('catformList'); //clear the cat form list
	}


  /**
   * Add customized javascript to the validating function
   * 
   * @return void
   **/	
    function addCustomJs($formName,$jsStr){
    	$this->customJs[$formName] = "\n//Custom JS \n".$jsStr;
     
    }




}


?>
