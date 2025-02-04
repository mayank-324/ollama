<?php defined('MW_PATH') || exit('No direct script access allowed'); ?>

<?php
/**
 * This hook allows to prepend content before the view file content.
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

if ($viewCollection->renderContent) {

    $hooks->doAction('before_active_form', $collection = new CAttributeCollection(array(
        'controller' => $this,
        'renderForm' => true,
    )));

    if ($collection->renderForm) {
        $form = $this->beginWidget('CActiveForm');
        ?>
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><?php echo $this->extension->t('Ollama Email Refiner Settings'); ?></h3>
            </div>
            <div class="box-body">
                <div class="form-group col-lg-2">
                    <?php echo $form->labelEx($model, 'enabled'); ?>
                    <?php echo $form->dropDownList($model, 'enabled', $model->getYesNoOptions(), $model->getHtmlOptions('enabled')); ?>
                    <?php echo $form->error($model, 'enabled'); ?>
                </div>
                <div class="form-group col-lg-10">
                    <?php echo $form->labelEx($model, 'ollama_prompt'); ?>
                    <?php echo $form->textField($model, 'ollama_prompt', $model->getHtmlOptions('ollama_prompt')); ?>
                    <?php echo $form->error($model, 'ollama_prompt'); ?>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="box-footer">
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...'); ?>">
                        <?php echo Yii::t('app', 'Save changes'); ?>
                    </button>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
        <?php
        $this->endWidget();
    }

    $hooks->doAction('after_active_form', new CAttributeCollection(array(
        'controller'   => $this,
        'renderedForm' => $collection->renderForm,
    )));
}

$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'      => $this,
    'renderedContent' => $viewCollection->renderContent,
)));
