<?php
/**
 * MagentoBoy
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   MagentoBoy
 * @package    MagentoBoy_DBAutoBackup
 * @copyright  Copyright (c) 2011 MagentoBoy (http://www.magentoboy.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * DBAutoBackup Cron Model
 *
 * @category   MagentoBoy
 * @package    MagentoBoy_DBAutoBackup
 * @author     MagentoBoy Team <magentoboy@gmail.com>
 */
class MagentoBoy_DBAutoBackup_Model_Cron extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_DBAUTOBACKUP_TEMPLATE     = 'system/dbautobackup/error_email_template';
    const XML_PATH_EMAIL_DBAUTOBACKUP_IDENTITY     = 'system/dbautobackup/error_email_identity';
    const XML_PATH_EMAIL_DBAUTOBACKUP_RECIPIENT    = 'system/dbautobackup/error_email';

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Send DBAutoBackup Warnings
     *
     * @return MagentoBoy_DBAutoBackup_Model_Cron
     */
    protected function _sendDBAutoBackupEmail()
    {
        if (!$this->_errors) {
            return $this;
        }
        if (!Mage::getStoreConfig(self::XML_PATH_EMAIL_DBAUTOBACKUP_RECIPIENT)) {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $emailTemplate = Mage::getModel('core/email_template');
        /* @var $emailTemplate Mage_Core_Model_Email_Template */
        $emailTemplate->setDesignConfig(array('area' => 'backend'))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_EMAIL_DBAUTOBACKUP_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_DBAUTOBACKUP_IDENTITY),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_DBAUTOBACKUP_RECIPIENT),
                null,
                array('warnings' => join("\n", $this->_errors))
            );

        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Backup Database
     *
     * @return MagentoBoy_DBAutoBackup_Model_Cron
     */
    public function backupDatabse()
    {
        $this->_errors = array();

        try {
            $backupDb = Mage::getModel('backup/db');
            $backup   = Mage::getModel('backup/backup')
                ->setTime(time())
                ->setType('db')
                ->setPath(Mage::getBaseDir("var") . DS . "backups");

            $backupDb->createBackup($backup);
        }
        catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTrace();
        }

        $this->_sendDBAutoBackupEmail();

        return $this;
    }
}
