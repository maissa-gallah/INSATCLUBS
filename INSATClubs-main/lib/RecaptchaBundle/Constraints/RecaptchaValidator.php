<?php
namespace MonCaptcha\RecaptchaBundle\Constraints;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RecaptchaValidator extends ConstraintValidator{

    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var ReCaptcha
     */
    private $reCaptcha;

    /**
     * RecaptchaValidator constructor.
     * @param RequestStack $requestStack
     * @param \ReCaptcha\ReCaptcha $reCaptcha
     */
    public function __construct(RequestStack $requestStack, ReCaptcha $reCaptcha)
    {
        $this->requestStack = $requestStack;
        $this->reCaptcha = $reCaptcha;
    }

    public function validate($value, Constraint $constraint)
    {
        $request = $this->requestStack->getCurrentRequest();
        $recaptchaResponse= $request->request->get('g-recaptcha-response');
       if(empty($recaptchaResponse))
       {
           $this->addViolation($constraint);
           return;
       }
      $response= $this->reCaptcha
           ->setExpectedHostname($request->getHost())
           ->verify($recaptchaResponse,$request->getClientIp());
       if(!$response->isSuccess())
       {
           $this->addViolation($constraint);
       }

    }
    public function addViolation (Constraint $constraint){
        $this->context->buildViolation($constraint->message)->addViolation();
    }
}