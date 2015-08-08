<?php

namespace SoundNormalizer;

class Core
{
	private $_f3;

	public function __construct($f3)
	{
		$this->_f3 = $f3;
	}

	public function isAlreadyConverting()
	{
		$conversions_query = $this->_f3->get("DB")->prepare("SELECT `ID` FROM `conversions` WHERE (`IP` = :IP AND `Completed` = '0')");
		$conversions_query->bindValue(":IP", Utilities::getIP());
		$conversions_query->execute();
		$conversions_results = $conversions_query->fetchAll();

		if (count($conversions_results) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function fetchRecentCompletedMatches($type, $id, $normalized)
	{
		if ($type == "youtube") {
			$duplicate_check_query = $this->_f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE (`YouTubeID` = :YouTubeID AND `DuplicateOf` = '0' AND `Normalized` = :Normalized AND `Completed` = '1' AND `StatusCode` = '3' AND `Deleted` = '0' AND `TimeCompleted` IS NOT NULL)");
			$duplicate_check_query->bindValue(":YouTubeID", $id);
		} elseif ($type == "upload") {
			$duplicate_check_query = $this->_f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE (`FileHash` = :FileHash AND `DuplicateOf` = '0' AND `Normalized` = :Normalized AND `Completed` = '1' AND `StatusCode` = '3' AND `Deleted` = '0' AND `TimeCompleted` IS NOT NULL)");
			$duplicate_check_query->bindValue(":FileHash", $id);
		}

		$duplicate_check_query->bindValue(":Normalized", $normalized);
		$duplicate_check_query->execute();

		return $duplicate_check_query->fetchAll();
	}

	public function insertConversion($type, $id, $normalized, $name = null, $local = null)
	{
		try {
			$insert_parameters = array();
			if ($type == "youtube") {						
				$insert_query = "INSERT INTO `conversions` (`Type`, `LocalName`, `YouTubeID`, `Normalized`, `IP`, `TimeAdded`) VALUES (:Type, :LocalName, :YouTubeID, :Normalized, :IP, :TimeAdded)";
				$insert_parameters[":YouTubeID"] = $id;
				$insert_parameters[":LocalName"] = Utilities::getRandomHash();			
			} elseif ($type == "upload") {
				$insert_query = "INSERT INTO `conversions` (`Type`, `LocalName`, `FileName`, `FileHash`, `Normalized`, `IP`, `TimeAdded`) VALUES (:Type, :LocalName, :name, :hash, :Normalized, :IP, :TimeAdded)";
				$insert_parameters[":name"] = $name;
				$insert_parameters[":hash"] = $id;
				$insert_parameters[":LocalName"] = $local;
			}
			
			$insert_parameters[":Type"] = $type;		
			$insert_parameters[":Normalized"] = $normalized;
			$insert_parameters[":IP"] = Utilities::getIP();
			$insert_parameters[":TimeAdded"] = time();

			$this->_f3->get("DB")->exec($insert_query, $insert_parameters);
		} catch (\PDOException $e) {
			$error_info = array(
				"parameters" => $insert_parameters,
				"error" => array(
					"message" => $e->getMessage(),
					"trace" => $e->getTrace()
				)
			);

			$this->_f3->get("log")->addError("Attempt at adding `" . $type . "` conversion", $error_info);
			$this->_f3->error("Database error.");
		}
	}

	public function insertDuplicateConversion($type, $duplicateOf)
	{
		try {
			$insert_query = "INSERT INTO `conversions` (`Type`, `DuplicateOf`) VALUES (:Type, :DuplicateOf)";
			$insert_parameters = array();
			$insert_parameters[":DuplicateOf"] = $duplicateOf;

			if ($type == "youtube") {			
				$insert_parameters[":Type"] = "youtube";			
			} else {
				$insert_parameters[":Type"] = "upload";
			}

			$this->_f3->get("DB")->exec($insert_query, $insert_parameters);
		} catch (\PDOException $e) {
			$error_info = array(
				"parameters" => $insert_parameters,
				"error" => array(
					"message" => $e->getMessage(),
					"trace" => $e->getTrace()
				)
			);

			$this->_f3->get("log")->addError("Attempt at adding duplicate `" . $type . "` conversion", $error_info);
			$this->_f3->error("Database error.");
		}
	}

	public function getConversion($id) {
		$conversion_query = $this->_f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE `IP` = :IP");
		$conversion_query->bindValue(":IP", Utilities::getIP());
		$conversion_query->execute();

		if ($conversion_query->rowCount() > 0) {
			return $conversion_query->fetch(\PDO::FETCH_ASSOC);
		} else {
			return false;
		}
	}

	public function getDownload()
	{
		$download_query = $this->_f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE (`IP` = :IP AND `Completed` = '1' AND `StatusCode` = '3' AND `Deleted` = '0') ORDER BY `ID` DESC LIMIT 1");
		$download_query->bindValue(":IP", Utilities::getIP());
		$download_query->execute();

		if ($download_query->rowCount() > 0) {
			$download_result = $download_query->fetch(\PDO::FETCH_ASSOC);

			$duplicate_id = $download_result["DuplicateOf"];
			if ($duplicate_id > 0) {
				return $this->getConversion($duplicate_id);
			} else {
				return $download_result;
			}
		} else {
			return false;
		}
	}
}