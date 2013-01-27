<?php 
class EM_Productsfilterwidget_Block_Action extends Mage_Adminhtml_Block_Widget_Grid 
{

	 /**
     * Prepare chooser element HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element Form Element
     * @return Varien_Data_Form_Element_Abstract
     */
		
	public function __construct($arguments=array())
    {
        parent::__construct($arguments);
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    } 

	
	protected function _prepareForm()
	{			
		$model = Mage::registry('current_promo_quote_rule');
		$form = new Varien_Data_Form();
		$this->setForm($form);
		print_r($form);
		$this->initFormCondition($form);    
			
		return parent::_prepareForm();
	} 
	
	public function split_content($tag_start,$tag_end,$str)
	{
		$temp	=	'';
		$temp1	=	'';
		$result	=	'';
		$temp	=	explode($tag_start,$str);
		if(count($temp)>2)
		{
			for($i=1;$i<count($temp);$i++)
			{
				$temp1		=	explode($tag_end,$temp[$i]);
				$result[]	=	$temp1[0];
			}
		}
		else
		{
			$temp1	=	explode($tag_end,$temp[1]);
			$result	=	$temp1[0];
		}		
		return $result;
	}	
 
	public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {		
        $uniqId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('*/promo_quote/chooser', array('uniq_id' => $uniqId));

        $chooser = $this->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId);

        if ($element->getValue()) {
            $rule = Mage::getModel('salesrule/rule')->load((int)$element->getValue());
            if ($rule->getId()) {
                $chooser->setLabel($rule->getName());
            }
        }
		
		
		$tam	=	$this->getRequest()->getPost('widget');
		if($tam)
		{						
			$plit	=	$this->split_content('conditions','}}',$tam);
			$cond	=	str_replace(':','',str_replace('"','',$plit));		
			$plit	=	explode('-',$cond);
			$count=count($plit);
			if($count>=3)
			{	$cond = $plit[0];
				for($ii=1;$ii<=$count-2;$ii++)
				{
					$cond.='-'.$plit[$ii];
				}
			}
			else
			{
				$cond	=	$plit[0];	
			}			
		}
		else
		{
			$params	=	Mage::registry('current_widget_instance')->getData('widget_parameters');			
			$plit	=	$this->split_content('conditions"','-',$params);
			$plit	=	explode('"',$plit);
			print_r($plit);die;
			$cond	=	$plit[1];			
		}
		
		$url = Mage::helper("adminhtml")->getUrl('productsfilterwidget/widget/form');
		$load = "<span id = 'loadimage' style='display:none'>
			<img src='".Mage::getBaseUrl('media')."em_productsfilter/loading.gif' alt='Loadding ...'>
			</span>";	
		$button = "<button type='button' onclick='return refe(\"$url\");'><span><span>Apply Conditions</span></span></button>$load<input type='text' id='success_string' style='margin-left:20px;border:none;width:60%' />";
		
		$element->setData('after_element_html', $this->initFormCondition($cond).$button);
        return $element;
    }

   
	public function initFormCondition($cond)
    {		
        $form = new Varien_Data_Form();
					
       	//$aa	=	Mage::getModel('productsfilterwidget/rule');		
		//echo 'aaa<pre>';print_r($aa);
		
		$model = Mage::getModel('productsfilterwidget/rule');        
        $conditions	=	Mage::helper('core')->urlDecode($cond);
		 $actionsArr = unserialize($conditions);
         if (!empty($actionsArr) && is_array($actionsArr)) {
             $model->getActions()->loadArray($actionsArr);
         }
         
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $model->getActions()->setJsFormObject('rule_actions_fieldset');
		$url = $this->getUrl('*/promo_quote/newActionHtml/form/rule_actions_fieldset'); 			
         $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
           ->setTemplate('em_productsfilter/fieldset.phtml')
           ->setNewChildUrl($url);
		
        $fieldset = $form->addFieldset('rule_actions_fieldset', array(
            'legend'=>Mage::helper('productsfilterwidget')->__('')
        ))->setRenderer($renderer);

        $fieldset->addField('actions', 'text', array(
            'name' => 'actions',
            'label' => Mage::helper('salesrule')->__('Apply To'),
            'title' => Mage::helper('salesrule')->__('Apply To'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/actions'));

        Mage::dispatchEvent('adminhtml_block_salesrule_actions_prepareform', array('form' => $form));

        $form->setValues($model->getData());
		
		return $fieldset->toHtml();
    }
}
?>