<?php

namespace App\Service;

use App\Helper\HeaderMatcherHelper;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Pimcore\Model\DataObject\Data\ExternalImage;
use Pimcore\Model\DataObject\Data\GeoCoordinates;
use Pimcore\Model\DataObject\Location;
use Pimcore\Model\DataObject\Player;
use Pimcore\Model\DataObject\Team;
use Pimcore\Model\Document;
use Pimcore\Model\Element\Service;

class DataImportService
{
    private const TEAMS_SHEET_NAME = 'Teams';
    private string $filePath;
    public function importXlsxData(string $filePath): array
    {
        $wrongHeaders = [];
        $this->filePath = $filePath;
        $spreadsheet = IOFactory::load($filePath);
        $wrongHeaders += $this->createTeams($spreadsheet->getSheetByName(self::TEAMS_SHEET_NAME));

        return $wrongHeaders;
    }

    private function createTeams(Worksheet $sheet): array
    {
        $rows = $sheet->toArray();
        $headers = $this->getHeaders($rows[0], self::TEAMS_SHEET_NAME);
        $wrongHeaders = $this->markWrongHeaders($headers);
        unset($rows[0]);

        foreach ($rows as $row) {
            $teamInfo = array_combine($headers, $row);
            $this->createOrUpdateTeam($teamInfo);
        }

        return $wrongHeaders;
    }

    private function getHeaders(array $headerRow, string $type = null): array
    {
        $headers = [];

        foreach ($headerRow as $column) {
            $headers[] = $type
                ? HeaderMatcherHelper::getTeamHeaderKey($column)
                : HeaderMatcherHelper::getPlayerHeaderKey($column)
            ;
        }

        return $headers;
    }

    private function createOrUpdateTeam(array $teamInfo)
    {
        $teamObject = $this->searchForExistingTeam($teamInfo['zvrZahl']) ?: new Team();
        $team = $this->createOrUpdateTeamObject($teamObject, $teamInfo);
        $location = $this->createLocation($teamInfo, $team->getId());
        $team->setCity(Document::getById($location->getId()));
        $this->createPlayers($team);
        $team->save();
    }

    public function searchForExistingTeam(string $zvrZahl): ?Team
    {
        return Team::getByZvrZahl($zvrZahl, [
            'limit' => 1,
            'unpublished' => true,
        ]);
    }

    private function markWrongHeaders(array $headers): array
    {
        $wrongHeaders = [];

        foreach ($headers as $header) {
            if (HeaderMatcherHelper::isKeyUndefined($header)) {
                $wrongHeaders[] = HeaderMatcherHelper::removeMarker($header);
            }
        }

        return $wrongHeaders;
    }

    private function createOrUpdateTeamObject(Team $team, array $teamInfo): Team
    {
        $logo = new ExternalImage($teamInfo['logo']);

        $team->setName($teamInfo['name'])
            ->setLogo($logo)
            ->setFoundingYear(Carbon::createFromFormat('Y', $teamInfo['foundingYear']))
            ->setVenue($teamInfo['venue'])
            ->setZvrZahl($teamInfo['zvrZahl'])
            ->setTrainer($teamInfo['trainer'])
            ->setPublished(true)
            ->setParentId(1)
            ->setKey(Service::getValidKey($teamInfo['name'], 'object'))
        ;
        $team->save();

        return $team;
    }

    private function createLocation(array $teamInfo, int $parentId): Location
    {
        $location = new Location();

        $location->setCity($teamInfo['city'])
            ->setCoordinates(new GeoCoordinates($teamInfo['lat'], $teamInfo['lon']))
            ->setPublished(true)
            ->setParentId($parentId)
            ->setKey(Service::getValidKey($teamInfo['city'], 'object'))
        ;
        $location->save();

        return $location;
    }

    private function createPlayers(Team $team): void
    {
        $spreadsheet = IOFactory::load($this->filePath);
        $sheet = $spreadsheet->getSheetByName($team->getName());
        $rows = $sheet->toArray();
        $headers = $this->getHeaders($rows[0]);
        unset($rows[0]);

        foreach ($rows as $row) {
            $playerInfo = array_combine($headers, $row);
            $this->createPlayer($playerInfo, $team);
        }
    }

    private function createPlayer(array $playerInfo, Team $team): Player
    {
        $player = new Player();

        $player->setName($playerInfo['name'])
            ->setNumber($playerInfo['number'])
            ->setAge($playerInfo['age'])
            ->setPosition($playerInfo['position'])
            ->setParentId($team->getId())
            ->setTeam($team)
            ->setPublished(true)
            ->setKey(Service::getValidKey($playerInfo['name'], 'object'))
        ;
        $player->save();

        return $player;
    }
}
