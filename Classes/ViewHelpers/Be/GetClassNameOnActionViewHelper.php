<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Be;

use In2code\Luxletter\Utility\FrontendUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetClassNameOnActionViewHelper
 */
class GetClassNameOnActionViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('actionName', 'string', 'Given action name', true);
        $this->registerArgument('className', 'string', 'Classname to return if action fits', false, ' btn-primary');
        $this->registerArgument('fallbackClassName', 'string', 'Classname for another action', false, ' btn-default');
    }

    /**
     * Return className if actionName fits to current action
     *
     * @return string
     */
    public function render(): string
    {
        if ($this->getCurrentActionName() === $this->arguments['actionName']) {
            return $this->arguments['className'];
        }
        return $this->arguments['fallbackClassName'];
    }

    /**
     * @return string
     */
    protected function getCurrentActionName(): string
    {
        $actionName = FrontendUtility::getActionName();
        if ($actionName === '') {
            if (FrontendUtility::getModuleName() === 'Newsletter') {
                $actionName = 'dashboard';
            }
        }
        return $actionName;
    }
}
