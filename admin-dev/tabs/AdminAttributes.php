<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7465 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(_PS_ADMIN_DIR_.'/../classes/AdminTab.php');

class AdminAttributes extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'attribute';
	 	$this->className = 'Attribute';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
		$this->fieldImageSettings = array('name' => 'texture', 'dir' => 'co');

		parent::__construct();
	}

	/**
	 * Display form
	 */
	public function displayForm($token = NULL)
	{
		if (!Combination::isFeatureActive())
		{
			$this->displayWarning($this->l('This feature has been disabled, you can active this feature at this page:').' <a href="index.php?tab=AdminPerformance&token='.Tools::getAdminTokenLite('AdminPerformance').'#featuresDetachables">'.$this->l('Performances').'</a>');
			return;
		}
		
		parent::displayForm();

		if (!($obj = $this->loadObject(true)))
			return;
		$color = ($obj->color ? $obj->color : 0);
		$attributes_groups = AttributeGroup::getAttributesGroups($this->_defaultFormLanguage);
		$strAttributesGroups = '';
		echo '
		<script type="text/javascript">
			var attributesGroups = {';
		foreach ($attributes_groups AS $attribute_group)
			$strAttributesGroups .= '"'.$attribute_group['id_attribute_group'].'" : '.($attribute_group['group_type'] == 'color' ? '1' : '0'  ) .',';
		echo $strAttributesGroups.'};
		</script>
		<form action="'.self::$currentIndex.'&submitAdd'.$this->table.'=1&token='.($token ? $token : $this->token).'" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_attribute" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/asterisk.gif" />'.$this->l('Attribute').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlspecialchars($this->getFieldValue($obj, 'name', (int)($language['id_lang']))).'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		echo '
				<script type="text/javascript">
					var flag_fields = \'name\';
				</script>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, 'flag_fields', 'name', false, true);
		echo '
					<div class="clear"></div>
				</div>
				<label>'.$this->l('Group:').' </label>
				<div class="margin-form">
					<select name="id_attribute_group" id="id_attribute_group" onchange="showAttributeColorGroup(\'id_attribute_group\', \'colorAttributeProperties\')">';
		
		foreach ($attributes_groups AS $attribute_group)
			echo '<option value="'.$attribute_group['id_attribute_group'].'"'.($this->getFieldValue($obj, 'id_attribute_group') == $attribute_group['id_attribute_group'] ? ' selected="selected"' : '').'>'.$attribute_group['name'].'</option>';
		echo '
					</select><sup> *</sup>
				</div>';
		if (Shop::isMultiShopActivated())
		{
			echo '<label>'.$this->l('GroupShop association:').'</label><div class="margin-form">';
			$this->displayAssoShop('group_shop');
			echo '</div>';
		}
		echo '
				<script type="text/javascript" src="../js/jquery/jquery-colorpicker.js"></script>
				<div id="colorAttributeProperties" style="'.((Validate::isLoadedObject($obj) AND $obj->isColorAttribute()) ? 'display: block;' : 'display: none;').'">
					<label>'.$this->l('Color').'</label>
					<div class="margin-form">
						<input width="20px" type="color" data-hex="true" class="color mColorPickerInput" name="color" value="'.(Tools::getValue('color', $color) ? htmlentities(Tools::getValue('color', $color)) : '#000000').'" /> <sup>*</sup>
						<p class="clear">'.$this->l('HTML colors only (e.g.,').' "lightblue", "#CC6600")</p>
					</div>
					<label>'.$this->l('Texture:').' </label>
					<div class="margin-form">
						<input type="file" name="texture" />
						<p>'.$this->l('Upload color texture from your computer').'<br />'.$this->l('This will override the HTML color!').'</p>
					</div>
					<label>'.$this->l('Current texture:').' </label>
					<div class="margin-form">
						<p>'.(file_exists(_PS_IMG_DIR_.$this->fieldImageSettings['dir'].'/'.$obj->id.'.jpg')
							? '<img src="../img/'.$this->fieldImageSettings['dir'].'/'.$obj->id.'.jpg" alt="" title="" /> <a href="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'&deleteImage=1"><img src="../img/admin/delete.gif" alt="" title="'.$this->l('Delete').'" />'.$this->l('Delete').'</a>'
							: $this->l('None')
						).'</p>
					</div>
				</div>
				'.Module::hookExec('attributeForm', array('id_attribute' => $obj->id)).'
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAddattribute" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>
		<script>
			showAttributeColorGroup(\'id_attribute_group\', \'colorAttributeProperties\');
		</script>';
	}

	/**
	 * Manage page processing
	 */
	public function postProcess($token = NULL)
	{
		if (!Combination::isFeatureActive())
			return;
		
		
		Module::hookExec('postProcessAttribute',
		array('errors' => &$this->_errors)); // send _errors as reference to allow postProcessFeatureValue to stop saving process
		
		if (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
			 	if (isset($_POST[$this->table.$_POST['groupid'].'Box']))
			 	{
					$object = new $this->className();
					if ($object->deleteSelection($_POST[$this->table.$_POST['groupid'].'Box']))
						Tools::redirectAdmin(self::$currentIndex.'&conf=2'.'&token='.($token ? $token : $this->token));
					$this->_errors[] = Tools::displayError('An error occurred while deleting selection.');
				}
				else
					$this->_errors[] = Tools::displayError('You must select at least one element to delete.');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('submitAdd'.$this->table))
		{
			$id_attribute = (int)Tools::getValue('id_attribute');
			// Adding last position to the attribute if not exist
			if ($id_attribute <= 0)
			{
				$sql = 'SELECT `position`+1
						FROM `'._DB_PREFIX_.'attribute`
						WHERE id_attribute_group = '.(int)Tools::getValue('id_attribute_group').' 
						ORDER BY position DESC';
			// set the position of the new attribute in $_POST for postProcess() method
				$_POST['position'] = DB::getInstance()->getValue($sql);
			}
			// clean \n\r characters
			foreach ($_POST as $key => $value)
				if (stripos($key, 'name_') !== false)
					$_POST[$key] = str_replace ('\n', '', str_replace('\r', '', $value));
			parent::postProcess();
		}
		else
			parent::postProcess();
	}
	

}


