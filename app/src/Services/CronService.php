<?php 

namespace Services;

use Cron\CronExpression;

class CronService extends AbstractService {
    const CRON_START_MESSAGE = 'Cron started successfully.';
    const CRON_STOP_MESSAGE  = 'Cron stopped successfully.';
	const DEFAULT_CRON_PATTERN = '@weekly';
	const DATETIME_FORMAT = 'Y-m-d H:i:s';
	
	/**
	 * getAll
	 *
	 * @return array
	 */
	public function getAll() : array
	{
		$config = $this->module->getConfig();
		return array_column($config["crons"], 'cron_name');
	}

	/**
	 * getDetails
	 *
	 * @param  string $name
	 * @return array
	 */
     public function getDetails(string $name = "") : array 
     {
		$config = $this->module->getConfig();
		
		// If no cron jobs are defined then return an empty array
		if (count($config["crons"]) == 0)
		{
			return [];
		}

		$cronIndex = 0;
		// If a name is specified then search for it (-1 if not found)
		if (strlen($name) > 0)
		{
			$cronIndex = array_search($name, array_column($config["crons"], 'cron_name'));
		}
		
		$cronConfig = [];
		// If the index is found... get the cron config
		if ($cronIndex >= 0)
		{
			$cronConfig = $config["crons"][$cronIndex];
			$cronConfig = array_merge($cronConfig, [
				"last_start_time" => $this->getLastStartTime()
			]);
		}

		return $cronConfig; 
	}

    /**
	 * getLastStartTime
	 *
	 * @return string
	 */
	public function getLastStartTime() : ? string
	{
		$lastStartTime = null;

		$message = CronService::CRON_START_MESSAGE;
		$sql 	 = "select max(timestamp) where message = '{$message}'";

		$results = $this->module->queryLogs($sql, []);
		if ($results && $results->num_rows > 0)
		{
			list($lastStartTime) = $results->fetch_row();

		}

		return $lastStartTime;
	}
	
	/**
	 * getSchedule
	 *
	 * @param  mixed $pattern
	 * @return array
	 */
	public function getSchedule($lastRunTime, $pattern = CronService::DEFAULT_CRON_PATTERN) : array
	{
		$pattern		= (CronService::isValidPattern($pattern)) ? $pattern : CronService::DEFAULT_CRON_PATTERN;
		$cron 			= new CronExpression($pattern);
		$currentTime 	= new DateTime();

		// If the last run time is an actual date
		if (CronService::isValidDate($lastRunTime))
		{
			// then, calculate the next run date
			$lastRunTime = new DateTime($lastRunTime);
			$nextRunTime = $cron->getNextRunDate($lastRunTime);
		}
		else
		{
			// otherwise the next runtime is now (current time)
			$lastRunTime = null;
			$nextRunTime = $currentTime;
		}

		$isNextRunDue = $nextRunTime->getTimestamp() <= $currentTime->getTimestamp();

		return [
			"pattern" 		=> $pattern,
			"expression" 	=> $cron->getExpression(),
			"last_run_time" => ($lastRunTime === null) ? '' : $lastRunTime->format(CronService::DATETIME_FORMAT),
			"next_run_time" => $nextRunTime->format(CronService::DATETIME_FORMAT),
			"current_time" 	=> $currentTime->format(CronService::DATETIME_FORMAT),
			"is_due" => $isNextRunDue
		];
	}

    /**
	 * getLogs
	 *
	 * @param  string $name
	 * @return array
	 */
    public function getLogs() : array
    {
		$sql = "select log_id, timestamp, user, ip, message order by timestamp desc limit 100";

		$logs 	 = [];
		$results = $this->module->queryLogs($sql, []);
		if ($results && $results->num_rows > 0)
		{
			$logs = $results->fetch_all(MYSQLI_ASSOC);
		}
        return $logs;
    }

    
    /**
     * logStart
     *
     * @return void
     */
    public function logStart()
    {
		$this->module->log(CronService::CRON_START_MESSAGE);
        return;
    }
    
    /**
     * logStop
     *
     * @return void
     */
    public function logStop()
    {
		$this->module->log(CronService::CRON_STOP_MESSAGE);
        return;
    }
	
	/**
	 * isValidDate
	 *
	 * @param  mixed $date
	 * @param  mixed $strict
	 * @return bool
	 */
	static public function isValidDate($date, $strict = true) : bool
	{
		if ($date === null) return false;

		$dateTime = DateTime::createFromFormat(CronService::DATETIME_FORMAT, $date);
		if ($strict) {
			$errors = DateTime::getLastErrors();
			if (!empty($errors['warning_count'])) {
				return false;
			}
		}
		return $dateTime !== false;
	}

	
	/**
	 * isValidPattern
	 *
	 * @param  mixed $pattern
	 * @return bool
	 */
	static public function isValidPattern($pattern) : bool 
	{
		if ($pattern !== null)
		{
			return CronExpression::isValidExpression($pattern);
		} 
		return false;
	}

}