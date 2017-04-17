<?php

namespace LemoForm\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormElement;

class FormControl extends AbstractHelper
{
    /**
     * List of elements with value options
     *
     * @var array
     */
    protected $elementsValueOptions = array(
        'multi_checkbox',
        'multicheckbox',
        'radio',
    );

    /**
     * @var FormElement
     */
    protected $helperFormElement;

    /**
     * @var FormControlAddon
     */
    protected $helperFormControlAddon;

    /**
     * @var FormControlButton
     */
    protected $helperFormControlButton;

    /**
     * @var FormControlHelpBlock
     */
    protected $helperFormControlHelpBlock;

    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|FormControl
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }
    /**
     * Render
     *
     * @param ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $helperFormElement = $this->getHelperFormElement();
        $helperFormControlAddon = $this->getHelperFormControlAddon();
        $helperFormControlButton = $this->getHelperFormControlButton();
        $helperFormControlHelpBlock  = $this->getHelperFormControlHelpBlock();

        $id = $this->getId($element);
        $id = trim(strtr($id, array('[' => '-', ']' => '')), '-');

        $type = strtolower($element->getAttribute('type'));
        $classCheckboxOrRadio = null;
        $content = '';

        // Renderovani datumu dle locale
        if ($element->getValue() instanceof \DateTime) {
            $formatter = new \IntlDateFormatter(
                \Locale::getDefault(),
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE,
                $element->getValue()->getTimezone()->getName(),
                \IntlDateFormatter::GREGORIAN
            );

            $element->setValue(str_replace(' ', '', $formatter->format($element->getValue())));
        }

        // Add class to value options for multicheckbox and radio elements
        if (in_array($type, $this->elementsValueOptions)) {
            $classCheckboxOrRadio = ($type === 'radio') ? 'radio' : 'checkbox';

            $content = '<div class="' . $classCheckboxOrRadio . '">';
        }

        $element->setAttribute('id', $id);

        if (null !== $element->getOption('addon') || null !== $element->getOption('button') || null !== $element->getOption('append') || null !== $element->getOption('prepend')) {
            $content .= '<div class="input-group input-group-sm">' . PHP_EOL;
        }

        // Addon - Pre
        if (null !== $element->getOption('prepend')) {
            $content .= $helperFormControlAddon($element->setOption('addon', $element->getOption('prepend'))) . PHP_EOL;
        }

        // Element
        $content .= $helperFormElement($element) . PHP_EOL;

        // Addon - Post
        if (null !== $element->getOption('append')) {
            $content .= $helperFormControlAddon($element->setOption('addon', $element->getOption('append'))) . PHP_EOL;
        }

        // Button
        if (null !== $element->getOption('button')) {
            $content .= $helperFormControlButton($element, $element->getOption('button')) . PHP_EOL;
        }

        if (in_array($type, $this->elementsValueOptions)) {
            $content = str_replace('/label><label', '/label></div><div class="' . $classCheckboxOrRadio . '"><label', $content);
            $content .= '</div>' . PHP_EOL;
        }

        if (null !== $element->getOption('button') || null !== $element->getOption('append') || null !== $element->getOption('prepend')) {
            $content .= '</div>' . PHP_EOL;
        }

        if (true === self::$renderErrorMessages && count($element->getMessages()) > 0) {
            $helpBlock = $element->getOption('help-block');

            $messages = [];
            if (!empty($helpBlock)) {
                $messages[] = '<span class="text-muted">' . $helpBlock . '</span>';
            }

            foreach ($element->getMessages() as $message) {
                if (is_array($message)) {
                    $messages[] = current($message);
                } else {
                    $messages[] = $message;
                }
            }
            $element->setOption('help-block', implode('<br />', $messages));
        }

        $content .= $helperFormControlHelpBlock($element) . PHP_EOL;

        return $content;
    }

    /**
     * Retrieve the FormControlAddon helper
     *
     * @return FormControlAddon
     */
    protected function getHelperFormControlAddon()
    {
        if ($this->helperFormControlAddon) {
            return $this->helperFormControlAddon;
        }

        if (!$this->helperFormControlAddon instanceof FormControlAddon) {
            $this->helperFormControlAddon = new FormControlAddon();
        }

        $this->helperFormControlAddon->setTranslator($this->getTranslator());
        $this->helperFormControlAddon->setView($this->getView());

        return $this->helperFormControlAddon;
    }

    /**
     * Retrieve the FormControlButton helper
     *
     * @return FormControlButton
     */
    protected function getHelperFormControlButton()
    {
        if ($this->helperFormControlButton) {
            return $this->helperFormControlButton;
        }

        if (!$this->helperFormControlButton instanceof FormControlButton) {
            $this->helperFormControlButton = new FormControlButton();
        }

        $this->helperFormControlButton->setTranslator($this->getTranslator());
        $this->helperFormControlButton->setView($this->getView());

        return $this->helperFormControlButton;
    }

    /**
     * Retrieve the FormControlHelpBlock helper
     *
     * @return FormControlHelpBlock
     */
    protected function getHelperFormControlHelpBlock()
    {
        if ($this->helperFormControlHelpBlock) {
            return $this->helperFormControlHelpBlock;
        }

        if (!$this->helperFormControlHelpBlock instanceof FormControlHelpBlock) {
            $this->helperFormControlHelpBlock = new FormControlHelpBlock();
        }

        $this->helperFormControlHelpBlock->setTranslator($this->getTranslator());
        $this->helperFormControlHelpBlock->setView($this->getView());

        return $this->helperFormControlHelpBlock;
    }

    /**
     * Retrieve the FormElement helper
     *
     * @return FormElement
     */
    protected function getHelperFormElement()
    {
        if ($this->helperFormElement) {
            return $this->helperFormElement;
        }

        if (!$this->helperFormElement instanceof FormElement) {
            $this->helperFormElement = new FormElement();
        }

        $this->helperFormElement->setView($this->getView());

        return $this->helperFormElement;
    }
}
