<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if ( ! function_exists('bs_form_date'))
{
	/*
	 *  bs_form_date('date','日期选择','6,2,4');
	 */
	function bs_form_date($name,$title='日期选择',$width='6,3,3')
	{
		$arr = explode(',',$width);
		$form  = '<!-- BEGIN  日期选择 BODY -->';
		$form .= '<div class="form-group col-md-'.$arr[0].'">';
		$form .= '  <label class="control-label col-md-'.$arr[1].'">'.$title.'</label>';
		$form .= '  <div class="col-md-'.$arr[2].'">';
		$form .= '  <div data-date-viewmode="years" data-date-format="yyyy-mm-dd" ';
		$form .= '       class="input-group input-medium date date-picker">';
		$form .= '    <input type="text" name="'.$name.'" readonly="" class="form-control" placeholder="'.Date('Y-m-d').'">';
		$form .= '    <span class="input-group-btn">';
		$form .= '    <button type="button" class="btn default"><i class="icon-calendar"></i></button>';
		$form .= '    </span>';
		$form .= '</div></div></div>';
		$form .= '<!-- END  日期选择 BODY -->';
		return $form;
	}
	/*
		<!-- BEGIN  日期选择 BODY -->
		<div class="form-group col-md-6">
			<label class="control-label col-md-2">日期选择</label>
			<div class="col-md-4">
			<div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="12-02-2012" class="input-group input-medium date date-picker">
			    <input type="text" readonly="" class="form-control">
			    <span class="input-group-btn">
			    <button type="button" class="btn default"><i class="icon-calendar"></i></button>
			    </span>
			 </div>
			</div>
		</div>
		<!-- END  日期选择 BODY -->	 
	*/
}

if ( ! function_exists('bs_form_datese'))
{
	/*
	 *  bs_form_datese('date','日期选择','6,2,4');
	 */
	function bs_form_datese($name,$title='日期范围',$width='6,3,3')
	{
		$arr = explode(',',$width);
		$form  = '<!-- BEGIN  日期范围 BODY -->';
		$form .= '<div class="form-group col-md-'.$arr[0].'">';
		$form .= '  <label class="control-label col-md-'.$arr[1].'">'.$title.'</label>';
		$form .= '  <div class="col-md-'.$arr[2].'">';
		$form .= '  <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="'.Date('Y-m-d').'"';
		$form .= '       class="input-group input-medium input-daterange date-picker">';
		$form .= '    <input type="text" name="S_'.$name.'" readonly="" class="form-control" placeholder="'.Date('Y-m-d').'">';
		$form .= '    <span class="input-group-addon">到</span>';
		$form .= '    <input type="text" name="E_'.$name.'" readonly="" class="form-control" placeholder="'.Date('Y-m-d').'">';
		$form .= '</div></div></div>';
		$form .= '<!-- END  日期范围 BODY -->';
		return $form;
	}
	/*
		<!-- BEGIN  日期范围 BODY -->
		<div class="form-group col-md-6">
		<label class="control-label col-md-2">日期范围</label>
		<div class="col-md-4">
		 <div data-date-format="mm/dd/yyyy" data-date="10/11/2012" class="input-group input-large date-picker input-daterange">
		    <input type="text" name="from" class="form-control">
		    <span class="input-group-addon">到</span>
		    <input type="text" name="to" class="form-control">
		 </div>
		</div>
		</div>
		<!-- END  日期范围 BODY -->
	*/
}

if ( ! function_exists('bs_form_datetime'))
{
	/*
	 *  bs_form_datetime('date','时间日期选择','6,2,4');
	 */
	function bs_form_datetime($name,$title='时间日期选择',$width='6,3,3')
	{
		$arr = explode(',',$width);
		$form  = '<!-- BEGIN  时间日期选择 BODY -->';
		$form .= '<div class="form-group col-md-'.$arr[0].'">';
		$form .= '  <label class="control-label col-md-'.$arr[1].'">'.$title.'</label>';
		$form .= '  <div class="col-md-'.$arr[2].'">';
		$form .= '  <div ';
		$form .= '       class="input-group input-medium date datetime-picker">';
		$form .= '    <input type="text" name="'.$name.'" readonly="" class="form-control" placeholder="'.Date('Y-m-d').'">';
		$form .= '    <span class="input-group-btn">';
		$form .= '    <button type="button" class="btn default date-set"><i class="icon-calendar"></i></button>';
		$form .= '    </span>';
		$form .= '</div></div></div>';
		$form .= '<!-- END  时间日期选择 BODY -->';
		return $form;
	}
	/*
		<!-- BEGIN  时间日期选择BODY -->
		<div class="form-group">
		<label class="control-label col-md-2">时间日期选择</label>
		<div class="col-md-4">
		 <div data-date="2012-12-21T15:25:00Z" class="input-group date form_datetime">                                       
		    <input type="text" class="form-control" readonly="" size="16">
		    <span class="input-group-btn">
		    <button type="button" class="btn default date-set"><i class="icon-calendar"></i></button>
		    </span>
		 </div>
		</div>
		</div>
		<!-- END  时间日期选择 BODY -->
	*/
}

if ( ! function_exists('bs_form_select'))
{
	/*
	 *  bs_form_select('date','下拉框',array('option1','option2'),'6,2,4');
	 */
	function bs_form_select($name,$title='下拉框',$data=array(),$field="name,name",$width='6,3,3')
	{
		$arr = explode(',',$width);
		$form  = '<!-- BEGIN  下拉框 BODY -->';
		$form .= '<div class="form-group col-md-'.$arr[0].'">';
		$form .= '  <label class="control-label col-md-'.$arr[1].'">'.$title.'</label>';
		$form .= '  <div class="col-md-'.$arr[2].'">';
		$form .= '  <select class="form-control" name="'.$name.'">';
		$form .= '     <option></option>';
		$arr = explode(',',$field);
		foreach ($data as $list){
			$form .= '<option value="'.$list[$arr[1]].'">'.$list[$arr[0]].'</option>';
		}
		$form .= '</select></div></div>';
		$form .= '<!-- END  下拉框 BODY -->';
		return $form;
	}
	/*
		<!-- BEGIN  下拉框 BODY -->
		<div class="form-group">
		<label class="col-md-2 control-label">下拉框</label>
		<div class="col-md-4">
		 <select class="form-control" name="">
		    <option>Option 1</option>
		    <option>Option 2</option>
		    <option>Option 3</option>
		    <option>Option 4</option>
		    <option>Option 5</option>
		 </select>
		</div>
		</div>
		<!-- END  下拉框 BODY -->
	*/
}

if ( ! function_exists('bs_form_text'))
{
	/*
	 *  bs_form_text('date','文本框','6,2,4');
	 */
	function bs_form_text($name,$title='文本框',$width='6,3,3')
	{
		$arr = explode(',',$width);
		$form  = '<!-- BEGIN  文本框 BODY -->';
		$form .= '<div class="form-group col-md-'.$arr[0].'">';
		$form .= '  <label class="control-label col-md-'.$arr[1].'" for="inputText">'.$title.'</label>';
		$form .= '  <div class="col-md-'.$arr[2].'">';
		$form .= '  <input type="text" placeholder="请输入" name="'.$name.'" class="form-control">';
		$form .= '</div></div>';
		$form .= '<!-- END  文本框 BODY -->';
		return $form;
	}
	/*
		<!-- BEGIN  文本框 BODY -->
		<div class="form-group">
		   <label class="col-md-2 control-label" for="inputText">文本框</label>
		   <div class="col-md-4">
		      <input type="text" placeholder="请输入" id="inputText" class="form-control">
		   </div>
		</div>
		<!-- END  文本框 BODY -->
	*/
}
/* End of file form_helper.php */
/* Location: ./application/helpers/boostrap/form_helper.php */
