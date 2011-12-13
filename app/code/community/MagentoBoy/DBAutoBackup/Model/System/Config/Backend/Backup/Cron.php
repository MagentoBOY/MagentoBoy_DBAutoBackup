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
 * DB Auto Backup Cron Backend Model
 *
 * @category   MagentoBoy
 * @package    MagentoBoy_DBAutoBackup
 * @author     MagentoBoy Team <magentoboy@gmail.com>
 */
class MagentoBoy_DBAutoBackup_Model_System_Config_Backend_Backup_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH = 'crontab/jobs/dbautobackup/schedule/cron_expr';

    /**
     * Cron settings after save
     *
     * @return MagentoBoy_DBAutoBackup_Model_System_Config_Backend_Backup_Cron
     */
    protected function _afterSave()
    {
        $enabled = $this->getData('groups/dbautobackup/fields/enabled/value');
        $time = $this->getData('groups/dbautobackup/fields/time/value');
        $frequency = $this->getData('groups/dbautobackup/fields/frequency/value');
 
        $frequencyDaily = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
        $frequencyWeekly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
        $frequencyMonthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

        if ($enabled) {
            $cronDayOfWeek = date('N');
            $cronExprArray = array(
                intval($time[1]),                                   # Minute
                intval($time[0]),                                   # Hour
                ($frequency == $frequencyMonthly) ? '1' : '*',      # Day of the Month
                '*',                                                # Month of the Year
                ($frequency == $frequencyWeekly) ? '1' : '*',       # Day of the Week
            );
            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '';
        }

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('dbautobackup')->__('Unable to save the cron expression.'));
        }
    }
}
