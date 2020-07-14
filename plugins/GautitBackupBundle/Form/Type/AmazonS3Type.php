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
 * Class AmazonS3Type.
 */
class AmazonS3Type extends AbstractType
{
	public static $allOptions;

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('amazons3Key', 'text', [
           
            'label'       => 'PLUGIN.GAUTIT.LABEL.AMAZONS3.KEY',
            'required'    => true,
            'label_attr'  => ['class' => 'control-label'],
            'attr'        => ['class' => 'form-control'],
			'constraints' => [
						new NotBlank([
							'message' => 'mautic.core.value.required',
						])
			],
			'data'=> (isset(self::$allOptions['amazons3Key']) && !empty(self::$allOptions['amazons3Key'])) ? self::$allOptions['amazons3Key'] :''

        ]);

        $builder->add('amazons3Secret', 'text', [
            'label'       => 'PLUGIN.GAUTIT.LABEL.AMAZONS3.SECRET',
            'required'    => true,
            'label_attr'  => ['class' => 'control-label'],
            'attr'        => ['class' => 'form-control'],
			'constraints' => [
						new NotBlank([
							'message' => 'mautic.core.value.required',
						])
			],
			'data'=> (isset(self::$allOptions['amazons3Secret']) && !empty(self::$allOptions['amazons3Secret'])) ? self::$allOptions['amazons3Secret'] :''
        ]);

        $builder->add('amazons3bucket', 'text', [
			'label'       => 'PLUGIN.GAUTIT.LABEL.AMAZONS3.BUCKET',
            'required'    => true,
            'label_attr'  => ['class' => 'control-label'],
            'attr'        => ['class' => 'form-control'],
			'constraints' => [
						new NotBlank([
							'message' => 'mautic.core.value.required',
						])
			],
			'data'=> (isset(self::$allOptions['amazons3bucket']) && !empty(self::$allOptions['amazons3bucket'])) ? self::$allOptions['amazons3bucket'] :''
		]);
        /*$builder->add('amazons3upload', CheckboxType::class, [
			'label'       => 'PLUGIN.GAUTIT.LABEL.AMAZONS3.UPLOAD',
            'required'    => false,
            'label_attr'  => ['class' => 'control-label'],
			'value' =>'amazons3',
			'data'=> (isset(self::$allOptions['amazons3upload']) && !empty(self::$allOptions['amazons3upload'])) ? self::$allOptions['amazons3upload'] :false

		]);*/
        $builder->add('amazons3Submit', 'submit', [
            'attr'        => ['class' => 'btn btn-default btn-save btn-copy','value' =>'submit'],
			'label' => 'PLUGIN.GAUTIT.LABEL.SAVE',
			
		]);
	
        /*$builder->add('showShare', 'yesno_button_group', [
            'label' => 'mautic.integration.Facebook.share.showshare',
            'data'  => (!isset($options['data']['showShare'])) ? 1 : $options['data']['showShare'],
        ]);*/
    }


}
