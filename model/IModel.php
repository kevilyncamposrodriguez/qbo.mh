<?php

/**
 * Description of InvoiceController
 *
 * @author Kevin Campos
 */
interface IModel {

    public function __construct();

    public function all($data);

    public function create($data);

    public function update($data);

    public function search($data);

    public function deleted($data);
}
