<?php
namespace MauticPlugin\GautitBackupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Class DropboxType.
 */
class DropboxType extends AbstractType
{
	public static $allOptions;
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		
        $builder->add('dropboxKey', 'text', [
           
            'label'       => 'PLUGIN.GAUTIT.LABEL.DROPBOX.KEY',
            'required'    => true,
            'label_attr'  => ['class' => 'control-label'],
            'attr'        => ['class' => 'form-control'],
			'constraints' => [
						new NotBlank([
							'message' => 'mautic.core.value.required',
						]),
			],
			'data'=> (isset(self::$allOptions['dropboxKey']) && !empty(self::$allOptions['dropboxKey'])) ? self::$allOptions['dropboxKey'] :''
        ]);

        $builder->add('dropboxSecret', 'text', [
            'label'       => 'PLUGIN.GAUTIT.LABEL.DROPBOX.SECRET',
            'required'    => true,
            'label_attr'  => ['class' => 'control-label'],
            'attr'        => ['class' => 'form-control'],
			'constraints' => [
						new NotBlank([
							'message' => 'mautic.core.value.required',
						])
			],
			'data'=> (isset(self::$allOptions['dropboxSecret']) && !empty(self::$allOptions['dropboxSecret'])) ? self::$allOptions['dropboxSecret'] :''

        ]);

        $builder->add('dropboxAccessToken', 'text', [
			'label'       => 'PLUGIN.GAUTIT.LABEL.DROPBOX.ACCESSTOKEN',
            'required'    => true,
            'label_attr'  => ['class' => 'control-label'],
            'attr'        => ['class' => 'form-control'],
			'constraints' => [
						new NotBlank([
							'message' => 'mautic.core.value.required',
						])
			],
			'data'=> (isset(self::$allOptions['dropboxAccessToken']) && !empty(self::$allOptions['dropboxAccessToken'])) ? self::$allOptions['dropboxAccessToken'] :''
		]);
		/*$builder->add('dropboxupload', CheckboxType::class, [
			'label'       => 'PLUGIN.GAUTIT.LABEL.DROPBOX.UPLOAD',
            'required'    => false,
            'label_attr'  => ['class' => 'control-label'],
			'value' =>'dropbox',
			'data'=> (isset(self::$allOptions['dropboxupload']) && !empty(self::$allOptions['dropboxupload'])) ? self::$allOptions['dropboxupload'] :false
		]);
		*/
        $builder->add('dropboxSubmit', 'submit', [
            'attr'        => ['class' => 'btn btn-default btn-save btn-copy','value' =>'submit'],
			'label' => 'PLUGIN.GAUTIT.LABEL.SAVE',
			
		]);
	
        /*$builder->add('showShare', 'yesno_button_group', [
            'label' => 'mautic.integration.Facebook.share.showshare',
            'data'  => (!isset($options['data']['showShare'])) ? 1 : $options['data']['showShare'],
        ]);*/
    }


}
