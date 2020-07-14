<?php

namespace MauticPlugin\GautitBackupBundle\Integration;


use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Integration\AbstractIntegration;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

/**
 * Class GautitBackupIntegration.php
 */
class GautitBackupIntegration extends AbstractIntegration
{
	const INTEGRATION_NAME = 'GautitBackup';
	const DISPLAY_NAME = 'Gautit Backup';
	

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::INTEGRATION_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return self::DISPLAY_NAME;
    }

   /**
     * Return's authentication method such as oauth2, oauth1a, key, etc.
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        // Just use none for now and I'll build in "basic" later
        return 'none';
    }
    /**
     * @param Form|FormBuilder $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    { 
		
        if ('keys' === $formArea) {
         
            $builder->add(
                'license_key',
                TextType::class,
                [
                    'label'       => 'PLUGIN.GAUTIT.LABEL.GAUTIT.LICENSEKEY',
                    'required'    => false,
                    'attr'        => [
                        'class'   => 'form-control'
                    ],
					'constraints' => [
					],
                    'data'        => empty($data['license_key']) ? '' : $data['license_key'],
                ]
            );

            $formModifier = function (FormInterface $form, $data)  {
                $form->add(
                'l_active',
                HiddenType::class,
                [
                    'attr'        => [
                        'class'   => 'form-control'
                    ],
					'data'        => empty($data['l_active']) ? '' : $data['l_active'],
                ]
				
				);
                $form->add(
                'l_message',
                HiddenType::class,
                [
                    'attr'        => [
                        'class'   => 'form-control'
                    ],
					'data'        => empty($data['l_message']) ? '' : $data['l_message'],
                ]
				
				);
            };


			$builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function(FormEvent $event) use($formModifier) {
					 $data = $event->getData();
					 $formModifier($event->getForm(), $data);
				
                }
			);
			$builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                function(FormEvent $event) use($formModifier,$data) {
					 
					 $data = $event->getData();
					 $form=$event->getForm();
					if(isset($data['license_key']) && !empty($data['license_key']) && $data['license_key'] !=  $this->keys['license_key']){
						$domain =  $this->request->getHost();
						
						$this->getCurlClientHelper()->setOpt(CURLOPT_URL,'https://gautit.com/wp-json/api/gautitbackup/activate?license='.urlencode($data['license_key']).'&domain='.$domain);
						$this->getCurlClientHelper()->setOpt(CURLOPT_CUSTOMREQUEST, "GET"); 
						$this->getCurlClientHelper()->setOpt(CURLOPT_RETURNTRANSFER,true); 
						$this->getCurlClientHelper()->setOpt(CURLOPT_VERBOSE, false);
						$this->getCurlClientHelper()->setOpt(CURLOPT_ENCODING, '');
						$this->getCurlClientHelper()->setOpt(CURLOPT_TIMEOUT,60);
						$this->getCurlClientHelper()->setOpt(CURLOPT_MAXREDIRS,10);
						$response = $this->getCurlClientHelper()->exec();
					
						if($response){
							$res = json_decode($response);
							
							if($res->status  == false){
								$data['l_active'] = false;
								$data['l_message'] = $res->message;
								$data['license_key'] ='';
							}else if($res->status == true){
								$data['l_active'] = true;
								$data['l_message'] = $res->message;
							}else{
									
								$data['l_active'] = false;
								$data['l_message'] = $this->translator->trans('mautic.integration.form.feature.license.unknown');
								$data['license_key'] ='';
							}
						}else{
							
							$data['l_active'] = false;
							$data['l_message'] = $this->translator->trans('mautic.integration.form.feature.license.unknown');
							$data['license_key'] ='';
						}
						
					}else if(empty($data['license_key'])){
						$data['l_message'] ='';
						$data['l_active'] = false;
					}else{
						
						$data['l_message'] ='';
						$data['l_active'] = true;
					}
					
					 //$form->get('license_key')->addError(new FormError("form error message"));
					 $event->setData( $data);
					 $formModifier($event->getForm(), $data);
				
                }
			);
			$builder->addEventListener(
                FormEvents::SUBMIT,
                function(FormEvent $event) use($formModifier,$data) {
					
					 $data = $event->getData();
					
					 $form=$event->getForm();
					 if($data['l_active'] == true && $data['l_message'] != ''){
						$flashBag = $this->factory->get('session')->getFlashBag();
						$flashBag->add('notice', $data['l_message']);
					 }else if(!empty($data['l_message'])) {
						$form->get('license_key')->addError(new FormError($data['l_message']));
					 }
					$event->setData( $data);
					$formModifier($event->getForm(), $data);
				
                }
			);
		

			
        }else if ($formArea == 'features') {
         
        }
    }
	public function getAllSettings(){
		return $this->keys;
	}
	
	public function encryptKeys($keys){
		return $this->encryptApiKeys($keys);
	}
	
	 /**
     * Get the CurlClient helper.
     *
     * @return mixed
     */
    public function getCurlClientHelper()
    {
        static $helper;
        if (null === $helper) {
            $class  = '\\MauticPlugin\\GautitBackupBundle\\Helper\\CurlClientHelper';
            $helper = new $class();
        }

        return $helper;
    }
}


