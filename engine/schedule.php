<?php

namespace Engine;

/**
 * Schedule
 *
 * @author François LASSERRE
 * @copyright Copyright (c) 2016 All rights reserved.
 */
class Schedule
{
    /**
     * __construct
     *
     * @param Class $config
     * @access public
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->process();
    }

    /**
     * readFolder
     *
     * @param string $folder
     * @access public
     * @return array|false
     */
    public function readFolder($folder)
    {
        if ($handle = opendir($folder)) {
            $out = array();
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $out[] = $entry;
                }
            }
            closedir($handle);
            return $out;
        }
        return false;
    }

    /**
     * getTrack
     *
     * @param int $i
     * @param array $bac
     * @param array $playlist
     * @param array $rules
     * @access public
     * @return false|object
     */
    public function getTrack($i, $bac, $playlist, $rules)
    {
        $j = $i % count($bac);
        $artist_last_diff = array_slice($playlist['artists'], -$rules['separation']['artist'], $rules['separation']['artist']);
        $track_last_diff = array_slice($playlist['title'], -$rules['separation']['track'], $rules['separation']['track']);

        if (isset($bac[$j])) {
            $artists = $bac[$j]['artists'];
            foreach($artists as $artist) {
                foreach($artist_last_diff as $last_diff) {
                    if (in_array($artist, $last_diff)) {
                        if (DEBUG) {
                            echo "WARNING: artist ".$artist." already diff\n";
                        }
                        return false;
                    }
                }
            }
            if (in_array(strtolower($bac[$j]['title']), $track_last_diff)) {
                if (DEBUG) {
                    echo "WARNING: track ".strtolower($bac[$j]['title'])." already diff\n";
                }
                return false;
            }
            if (DEBUG) {
                echo "Track Add ".$bac[$j]['title']."\n";
                echo "Artist Add ".$bac[$j]['artist']."\n";
            }
            return $bac[$j];
        }

        return false;
    }

    /**
     * process
     *
     * @access public
     * @return void
     */
    public function process()
    {
        $config = $this->config;
        $cr = $config->getCr();
        $stations = $config->getStations();
        $nb_station = count($stations);

        if ($nb_station > 0) {

            foreach ($stations as $station) {

                $station_name = $station->getName();
                $rules = $station->getRules();
                if (DEBUG) {
                    echo "Station : ";
                    echo $station_name."\n";
                    echo "Rules : \n";
                    print_r($rules);
                }

                $toolong = false;
                $artist_list = array();
                $track_list = array();
                $bac = array();
                $playlist = array();
                $playlist['artist'] = array();
                $playlist['artists'] = array();
                $playlist['title'] = array();
                $playlist['filename'] = array();
                $playlist['duration'] = array();
                $folder = $config->getMusicFolder().$station_name.'/';

                $tracks = $this->readFolder($folder);

                foreach($tracks as $track) {
                    $path_track = pathinfo($track);

                    if (in_array($path_track['extension'], $config->getMusicExt())) {
                        list($artist, $title) = explode(' - ', $path_track['filename']);
                        $artists = preg_split('/(, | & )/', strtolower($artist));
                        foreach ($artists as $art) {
                            $artist_list[] = strtolower($art);
                        }
                        $track_list[] = strtolower($title);
                        $song = array();
                        $song['artist'] = $artist;
                        $song['artists'] = $artists;
                        $song['title'] = $title;
                        $song['filename'] = $folder.$track;
                        $song['duration'] = rand(135,305);
                        $bac[] = $song;
                    }
                }

                shuffle($bac);

                $max_artist = floor(count(array_unique($artist_list)));
                $max_track = floor(count(array_unique($track_list)));

                if (DEBUG) {
                    echo "max artist : ".$max_artist."\n";
                    echo "max track : ".$max_track."\n";
                    echo "max bac : ".count($bac)."\n";
                }

                $error = array();

                if (empty($config->getPlaylistPath())) {
                    $error[] = 'WARNING: playlist_path must be set in config.php file.';
                }

                if (empty($config->getPlaylistSize())) {
                    $error[] = 'WARNING: playlist_size must be set in config.php file.';
                }

                if (empty($rules['separation']['artist'])) {
                    $error[] = 'WARNING: artist_separation must be set in separation.php file.';
                }

                if (empty($rules['separation']['track'])) {
                    $error[] = 'WARNING: track_separation must be set in separation.php file.';
                }

                if ($max_artist < $rules['separation']['artist']) {
                    $error[] = 'WARNING: artist_separation is too high: '.$rules['separation']['artist'].' use a value between 1 and '.$max_artist;
                }

                if ($max_track < $rules['separation']['track']) {
                    $error[] = 'WARNING: track_separation is too high: '.$rules['separation']['track'].' use a value between 1 and '.$max_track;
                }

                if (count($error)>0) {
                    foreach($error as $msg) {
                        echo $msg.$cr;
                    }
                    exit();
                }

                $i=0;

                do {

                    $track = $this->getTrack($i, $bac, $playlist, $rules);

                    if ($track !== false) {
                        $playlist['artist'][] = strtolower($track['artist']);
                        $playlist['artists'][] = $track['artists'];
                        $playlist['duration'][] = $track['duration'];
                        $playlist['filename'][] = $track['filename'];
                        $playlist['title'][] = strtolower($track['title']);
                    }

                    $i++;
                    if ($i % $max_track == 2) {
                        shuffle($bac);
                    }

                } while(count($playlist['artist']) < $config->getPlaylistSize());

                $m3u_content = '#EXTM3U'.$cr;

                foreach ($playlist['filename'] as $item) {
                    //$m3u_content .= '#EXTINF:'.$item->duration.', '.$item->artist.' - '.$item->title.$cr;
                    $m3u_content .= $item.$cr;
                }

                if (file_put_contents($config->getPlaylistPath().$station_name.'.m3u', $m3u_content) !== FALSE) {
                    echo 'Playlist '.$config->getPlaylistPath().$station_name.'.m3u saved.'.$cr;
                }
            }
        }
    }
}
