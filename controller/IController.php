<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Alberth Calderon Alvarado <albert.calderon@ucr.ac.cr>
 */
interface IController {

    //put your code here
    public function __construct();

    public function index();

    public function all();

    public function create();

    public function update();

    public function delete();

    public function search();
}
