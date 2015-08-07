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

	public function insertConversion($type, $id, $normalized)
	{
		if ($type == "youtube") {
			$insert_query = $this->_f3->get("DB")->prepare("INSERT INTO `conversions` (`Type`, `LocalName`, `YouTubeID`, `Normalized`, `IP`, `TimeAdded`) VALUES (:Type, :LocalName, :YouTubeID, :Normalized, :IP, :TimeAdded)");
			$insert_query->bindValue(":Type", "youtube");
			$insert_query->bindValue(":LocalName", Utilities::getRandomHash());
			$insert_query->bindValue(":YouTubeID", $id);
			$insert_query->bindValue(":Normalized", $normalized);
			$insert_query->bindValue(":IP", Utilities::getIP());
			$insert_query->bindValue(":TimeAdded", time());
		} elseif ($type == "upload") {
			// implement this weidi
		}

		$insert_query->execute();
	}

	public function insertDuplicateConversion($type, $duplicateOf)
	{
		if ($type == "youtube") {
			$insert_query = $this->_f3->get("DB")->prepare("INSERT INTO `conversions` (`Type`, `DuplicateOf`) VALUES (:Type, :DuplicateOf)");
			$insert_query->bindValue(":Type", "youtube");
			$insert_query->bindValue(":DuplicateOf", $duplicateOf);
			$insert_query->execute();
		} else {
			// implement this weidi
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