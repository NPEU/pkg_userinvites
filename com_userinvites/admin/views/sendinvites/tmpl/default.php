<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$action = 'index.php?option=com_userinvites&amp;view=sendinvites';
$fieldsets = $this->form->getFieldsets();

$option         = JRequest::getCmd('option');
$params         = JComponentHelper::getParams($option);
$email_template = $params->get('template');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		console.log(task);
		if (task == 'sendinvites.save' || document.formvalidator.isValid(document.id('sendinvites-form')))
		{
			Joomla.submitform(task, document.getElementById('sendinvites-form'));
		}
	}
</script>
<form action="<?php echo JRoute::_($action); ?>"
	  method="post"
      name="adminForm"
      id="sendinvites-form"
      class="form-validate form-horizontal"
>
	<div class="row-fluid">
	<!-- Begin Content -->
		<div class="span12 form-horizontal">
			<ul class="nav nav-tabs">
				<?php $i=0; foreach ($fieldsets as $fieldset): $i++; ?>
				<li<?php echo $i == 1 ? ' class="active"' : ''; ?>><a href="#<?php echo $fieldset->name; ?>" data-toggle="tab"><?php echo JText::_($fieldset->label);?></a></li>
				<?php endforeach; ?>
			</ul>
			<div class="tab-content">
			<?php $i=0; foreach ($fieldsets as $fieldset): $i++; ?>
			<?php $form_fieldset = $this->form->getFieldset($fieldset->name); ?>
				<!-- Begin Tabs -->
				<div class="tab-pane<?php echo $i == 1 ? ' active' : ''; ?>" id="<?php echo $fieldset->name; ?>">
					<?php $hidden_fields = array(); foreach($form_fieldset as $field): ?>
					<?php if($field->type == 'Hidden'){$hidden_fields[] = $field->input; continue;} ?>
					<div class="control-group">
						<?php if ($field->type != 'Button'): ?>
						<div class="control-label">
							<?php echo JText::_($field->label); ?>
						</div>
						<?php endif; ?>
						<div class="controls">
                            <?php if ($field->name == 'jform[email_body]'){$field->value = $email_template;} ?>
							<?php echo $field->input; ?>
						</div>
					</div><!-- End control-group -->
					<?php endforeach; ?>
					<?php echo implode("\n", $hidden_fields); ?>
				</div><!-- End tab-pane -->
			<?php endforeach; ?>
			</div><!-- End tab-content -->
		</div><!-- End span12 form-horizontal -->
	</div><!-- End row-fluid -->
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>