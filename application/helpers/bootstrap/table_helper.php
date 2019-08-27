<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * bootstrap datatable helper
 *
 * @access	public
 * @return	string
 */
if (!function_exists('bs_datatable')) {
    /*
     *  bs_datatable($this->datagridinfo,$data);
     */

    function bs_datatable($define, $data = array()) {
        $form = '<!-- BEGIN DATATABLE-->';
        $form .= '<div class="portlet-body">';
        $form .= '<table class="table table-striped table-bordered table-hover " id="models-data" >';
        $form .= '<thead><tr>';
        $form .= '<th style="width:8px;"></th>';
        $define = $define['listfield'];
        foreach ($define as $arr) {
            $form .= '<th';
            if (isset($arr['class']))
                $form .=' class="' . $arr['class'] . '" ';
            if (isset($arr['style']))
                $form .=' style="' . $arr['style'] . '" ';
            $form .= '>' . $arr['dispname'] . '</th>';
        }
        $form .= '</tr></thead><tbody>';
        foreach ($data as $arr) {
            $form .= '<tr><td><input type="checkbox" type="checkbox" value="1" /></td>';
            foreach ($define as $arr1) {
                $form .= '<td';
                if (isset($arr['class']))
                    $form .=' class="' . $arr['class'] . '" ';
                if (isset($arr['style']))
                    $form .=' style="' . $arr['style'] . '" ';
                $form .= '>' . $arr[$arr1['fieldname']] . '</td>';
            }
            $form .= '</tr>';
        }
        $form .= '</tbody>';
        $form .= '<div class="portlet-body"></table></div>';
        $form .= '<!-- END DATATABLE-->';
        return $form;
    }

    /*
      <div class="portlet-body">
      <table class="table table-striped table-bordered table-hover " id="models-data" >
      <thead>
      <tr>
      <th style="width:8px;"></th>
      <th>Username</th>
      <th class="hidden-480">Email</th>
      <th class="hidden-480">Points</th>
      <th class="hidden-480">Joined</th>
      <th ></th>
      </tr>
      </thead>
      <tbody>
      <tr class="odd gradeX">
      <td><input type="checkbox" class="checkboxes" value="1" /></td>
      <td>shuxer</td>
      <td class="hidden-480"><a href="mailto:shuxer@gmail.com">shuxer@gmail.com</a></td>
      <td class="hidden-480">120</td>
      <td class="center hidden-480">12 Jan 2012</td>
      <td ><span class="label label-sm label-success">Approved</span></td>
      </tr>
      </tbody>
      </table>
      </div>
     */
}

if (!function_exists('bs_tableinit')) {
    /*
     *  bs_tableinit($this->datagridinfo);
     */

    function bs_tableinit($define) {
        $aoColumns = array();
        $aoColumnDefs = array();
        $aaSorting = array();
        $mData = array();
        $mData['mData'] = null;
        array_push($aoColumns, $mData);
        $i = 0;
        foreach ($define['listfield'] as $item) {
            $i ++;
            $mData = array();
            $mData['mData'] = $item['fieldname'];
            if (isset($item['sortable'])) {
                if (false == $item['sortable']) {
                    $mData['asSorting'] = array();
                }
            }
            array_push($aoColumns, $mData);
            if (isset($item['type'])&&($item['type']=='hide')){
                $arr = array();
                $arr['bSearchable'] = false;
                $arr['bVisible'] = false;
                $arr['aTargets'] = array($i);
                array_push($aoColumnDefs, $arr);
            }
            if (isset($item['order'])){
                $arr = array($i,$item['order']);
                array_push($aaSorting, $arr);
            }        }
        $str = "aoColumns:" . json_encode($aoColumns);
        if (sizeof($aoColumnDefs) > 0) {
            $str .= ",aoColumnDefs:" . json_encode($aoColumnDefs);
        }
        if (sizeof($aaSorting) > 0) {
            $str .= ",aaSorting:" . json_encode($aaSorting);
        }
        return $str;
    }

    /*
     *  order=desc
     *  type=hide
     *  sortable = false
     */
    /*
      "aoColumns": [
      { "mData": null },
      { "mData": "id" },
      { "mData": "account" },
      { "mData": "fullname" },
      { "mData": "city" },
      ],
     */
}


/* End of file table_helper.php */
/* Location: ./application/helpers/boostrap/table_helper.php */
