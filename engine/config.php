<?php

namespace Engine;

/**
 * Config
 *
 * @author François LASSERRE
 * @copyright Copyright (c) 2016 All rights reserved.
 */
class Config
{
    private $config_music = [];
    private $config_playlist = [];
    private $config_stations = [];

    /**
     * __construct
     *
     * @param bool $music_ext
     * @param mixed $music_path
     * @param mixed $playlist_path
     * @param mixed $playlist_size
     * @access public
     * @return void
     */
    public function __construct($music_ext = array('mp3'), $music_path, $playlist_path, $playlist_size)
    {
        $this->config_music['ext'] = $music_ext;
        $this->config_music['path'] = $music_path;
        $this->config_playlist['path'] = $playlist_path;
        $this->config_playlist['size'] = $playlist_size;
    }

    /**
     * addStation
     *
     * @param Station $station
     * @access public
     * @return void
     */
    public function addStation(Station $station)
    {
        $this->config_stations[] = $station;
    }

    /**
     * getMusicExt
     *
     * @access public
     * @return void
     */
    public function getMusicExt()
    {
        return $this->config_music['ext'];
    }

    /**
     * getMusicFolder
     *
     * @access public
     * @return void
     */
    public function getMusicFolder()
    {
        return $this->config_music['path'];
    }

    /**
     * getPlaylistPath
     *
     * @access public
     * @return void
     */
    public function getPlaylistPath()
    {
        return $this->config_playlist['path'];
    }

    /**
     * getPlaylistSize
     *
     * @access public
     * @return void
     */
    public function getPlaylistSize()
    {
        return $this->config_playlist['size'];
    }

    /**
     * getStations
     *
     * @access public
     * @return void
     */
    public function getStations()
    {
        return $this->config_stations;
    }
}