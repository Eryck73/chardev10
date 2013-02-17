<?php

namespace chardev\tools;

use chardev\backend\Database;
use chardev\backend\DatabaseHelper;
use chardev\profiles\CommunityPlatformClient;

class DataRetriever
{

    protected $cpc;

    private static $race_name_to_id = array(
        "human" => 1,
        "orc" => 2,
        "dwarf" => 3,
        "night elf" => 4,
        "undead" => 5,
        "tauren" => 6,
        "gnome" => 7,
        "troll" => 8,
        "goblin" => 9,
        "blood elf" => 10,
        "draenei" => 11,
        "worgen" => 22,
        "pandaren" => array(25, 26)
    );

    private static $class_name_to_id = array(
        "warrior" => 1,
        "paladin" => 2,
        "hunter" => 3,
        "rogue" => 4,
        "priest" => 5,
        "death knight" => 6,
        "shaman" => 7,
        "mage" => 8,
        "warlock" => 9,
        "druid" => 11,
        "monk" => 10
    );

    function __construct()
    {
        $this->cpc = new CommunityPlatformClient(BNET_PRIVATE_KEY, BNET_PUBLIC_KEY);
    }

    public function retrieveItems($min = 0, $max = PHP_INT_MAX)
    {
        //
        // get item ids
        $stmt = DatabaseHelper::query(Database::getConnection(),
            "SELECT ID FROM chardev_mop.item WHERE ID >= ? AND ID <= ? ORDER BY ID DESC",
            array($min, $max));

        $insertStmt = Database::getConnection()->prepare(
            "REPLACE INTO chardev_mop_static.chardev_data_bnet_item VALUES (?,?)");

        while (true) {
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($record === false) {
                break;
            }

            $id = (int)$record["ID"];

            $retry = 0;
            do {
                $itm = $this->cpc->getItem($id);
            } while (!$itm && ++$retry < 3);
            $insertStmt->bindValue(1, $id);
            $insertStmt->bindValue(2, $itm);
            $insertStmt->execute();

            echo $id . "\n";
        }
    }

    public function retrieveProfiles(array $names)
    {
        $base = "http://eu.battle.net";

        foreach ($names as $name) {
            $page = 1;
            $next = false;

            echo "Search for: $name\n";
            do {
                echo "Page $page\n";
                $query = "{$base}/wow/en/search?q={$name}&f=wowcharacter&page={$page}";

                $contents = file_get_contents($query);

                if (preg_match("/data-pagenum=\"" . ($page + 1) . "\"/", $contents)) {
                    $next = true;
                } else {
                    $next = false;
                }

                preg_match_all('/<td[^>]*>\s*<a[^>]*href="(\/wow\/en\/character\/.*?)"[^>]*>(?:.|\s)*?<td[^>]*>\s*(\d+)\s*<\/td[^>]*>/', $contents, $matches);

                if (!$matches) {
                    break;
                }

                $chars = count($matches[1]);
                echo "Found $chars profiles\n";

                for ($i = 0; $i < $chars; $i++) {
                    if (((int)$matches[2][$i]) < 10) {
                        $next = false;
                        break;
                    }

                    $url = $base . $matches[1][$i];
                    echo "#$i: $url\n";
                    try {
                        $this->retrieveProfile($url, $name);
                    }
                    catch ( \Exception $e ) {
                        echo "Failed to retrieve profile $name from $url:\n";
                        echo $e->getTraceAsString() . "\n";
                    }
                }

                $page++;
            } while ($next);
        }
    }

    public function retrieveProfile($url, $name)
    {
        $old = error_reporting(1);
        $content = file_get_contents($url . 'simple');
        error_reporting($old);

        if (!$content) {
            echo "no content\n";
            return;
        }
        if (preg_match('/Character Not Available/', $content)) {
            echo "character not available\n";
            return;
        }

        $xml = simplexml_load_string($content);
        if (!$xml) {
            echo "invalid xml\n";
            return;
        }

        $race = $xml->xpath('//*[@class="race"]');
        $race = (string)$race[0];
        $race = self::$race_name_to_id[strtolower($race)];
        //
        // 2 races for pandaren
        if( is_array($race)) {
            if($xml->xpath('//*[@class="profile-wrapper profile-wrapper-horde"]')) {
                $race = $race[1];
            }
            else {
                $race = $race[0];
            }
        }
        //		class
        $class = $xml->xpath('//*[@class="class"]');
        $class = (string)$class[0];
        $class = self::$class_name_to_id[strtolower($class)];
        //		level
        $level = $xml->xpath('//*[@class="level"]');
        $level = $level[0]->strong;

        echo "Retrieved $name ({$race},{$class},{$level})\n";

        DatabaseHelper::execute(Database::getConnection(),
            "REPLACE INTO chardev_mop_static.chardev_data_bnet_profiles VALUES (?,?,?,?,?,?)",
            array($name, $url, $race, $class, $level, $content));
    }
}