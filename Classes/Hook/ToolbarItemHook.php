<?php

namespace Cabag\CabagLoginas\Hook;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use PDO;
use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Session\Backend\DatabaseSessionBackend;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class ToolbarItemHook
{

    protected $users = array();
    protected $EXTKEY = 'cabag_loginas';

    public function __construct()
    {
        $GLOBALS['LANG']->includeLLFile('EXT:cabag_loginas/Resources/Private/Language/locallang_db.xlf');
    }



    public function formatLinkText($user, $defLinkText)
    {
        foreach ($user as $key => $value) {
            $defLinkText = str_replace('#' . $key . '#', $value, $defLinkText);
        }

        return $defLinkText;
    }


    public function getHREF($user)
    {
        if (!MathUtility::canBeInterpretedAsInteger($user['uid'])) {
            return '#';
        }
        $parameterArray = array();
        $parameterArray['userid'] = (string)$user['uid'];
        $parameterArray['timeout'] = (string)$timeout = time() + 3600;
        // Check user settings for any redirect page
        if ($user['felogin_redirectPid']??false) {
            $parameterArray['redirecturl'] = $this->getRedirectUrl($user['felogin_redirectPid']);
        } else {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('fe_users');
            $userGroup = $queryBuilder
                ->select('fg.felogin_redirectPid')
                ->from('fe_users', 'fu')
                ->join(
                    'fu',
                    'fe_groups',
                    'fg',
                    'fg.uid in (fu.usergroup)'
                )->where('fg.felogin_redirectPid != \'\'', 'fu.uid = ' . $user['uid'])->executeQuery()
                ->fetchAssociative();

            $parameterArray['redirecturl'] = $this->getRedirectUrl($userGroup['felogin_redirectPid'] ?? $user['pid']);
        }
        $ses_id = $_COOKIE['be_typo_user'];
        $databaseSessionBackend = GeneralUtility::makeInstance(DatabaseSessionBackend::class);
        $hashedSesId = $databaseSessionBackend->hash($ses_id);
        $parameterArray['verification'] = md5($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] . $hashedSesId . serialize($parameterArray));
        $link = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . '?' . GeneralUtility::implodeArrayForUrl('tx_cabagloginas', $parameterArray);

        return $link;
    }



    public function getLoginAsIconInTable($user, $title = '')
    {
        $additionalClass = '';
        if (trim($title) === '') {
            $title = $GLOBALS['LANG']->getLL('cabag_loginas.switchToFeuser', true);
        }
        $iconFactory = GeneralUtility::makeInstance( IconFactory::class );
        $switchUserIcon = $iconFactory->getIcon('actions-system-backend-user-switch', Icon::SIZE_SMALL)->render();
        $additionalClass = '  class="btn btn-default"';
        $link = $this->getHREF($user);
        $content = '<a title="' . $title . '" href="' . $link . '" target="_blank"' . $additionalClass . '>' . $switchUserIcon . '</a>';
        return $content;
    }

    public function getLoginAsLinkInActions($user, $title = '')
    {
        $additionalClass = '';
        if (trim($title) === '') {
            $title = $GLOBALS['LANG']->getLL('cabag_loginas.switchToFeuser', true);
        }
        $iconFactory = GeneralUtility::makeInstance( IconFactory::class );
        $switchUserIcon = $iconFactory->getIcon('actions-system-backend-user-switch', Icon::SIZE_SMALL)->render();
        $additionalClass = '  class="dropdown-item"';
        $link = $this->getHREF($user);
        $content = '<a title="' . $title . '" href="' . $link . '" target="_blank"' . $additionalClass . '>' . $switchUserIcon . $title . '</a>';
        return $content;
    }



    /**
     * @param integer $pageId
     *
     * @return string
     */
    protected function getRedirectUrl($pageId)
    {
        return rawurlencode($this->getRealDomain($pageId) . '/index.php?id=' . $pageId);
    }



    /**
     * Get the the real domain of given pid.
     *
     * When outside a normal page tree (i.e. global storage), this returns the current domain in which the user
     * is logged in the backend.
     *
     * @param int $pageId
     * @return string
     */
    private function getRealDomain(int $pageId): string
    {
        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
            return parse_url($site->getRouter()->generateUri($pageId), PHP_URL_HOST);
        } catch(SiteNotFoundException $e) {
            // In some cases, when frontend users are outside a normal page tree (global storage)
            // just return the domain from which the user is logged in the backend
            return GeneralUtility::getIndpEnv('HTTP_HOST');
        }
    }

}
