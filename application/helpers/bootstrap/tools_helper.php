<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * bootstrap form helper
 *
 * @access	public
 * @return	string
 */
if ( ! function_exists('bs_breadcrumb'))
{
	/*
	 *  bs_breadcrumn('DEMO','demo/index',array('动作'=>'demo/test'));
	 */
	function bs_breadcrumb($title,$url="",$action=array())
	{
		$CI =& get_instance();
		$home = $CI->config->site_url();
		$CI->config->site_url($url);
		// If no action is provided then set to the current url
		$url OR $url = $CI->config->site_url($CI->uri->uri_string());

		$form = '<!-- BEGIN PAGE TITLE & BREADCRUMB-->';
		$form .= '<ul class="page-breadcrumb breadcrumb">';
		if (sizeof($action)>0) {
			$form .= '  <li class="btn-group">';
			$form .= '    <button type="button" class="btn blue dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">';
			$form .= '    <span>操作</span> <i class="icon-angle-down"></i></button>';
			$form .= '    <ul class="dropdown-menu pull-right" role="menu">';
			foreach($action as $key=>$arr)
				$form .= '      <li><a href="'.$arr.'">'.$key.'</a></li>';
			$form .= '    </ul>';
			$form .= '  </li>';
		}
		//$form .= '  <li><i class="icon-home"></i><a href="'.$home.'">首页</a><i class="icon-angle-right"></i></li>';
		$form .= '  <li><i class="icon-home"></i>首页<i class="icon-angle-right"></i></li>';
		$arr = explode('/', $title);
		if (sizeof($arr)>1)
			$form .= '  <li>'.$arr[0].'<i class="icon-angle-right"></i><a href="'.$url.'">'.$arr[1].'</a><i class="icon-angle-right"></i></li>' ;
		else
			$form .= '  <li><a href="'.$url.'">'.$title.'</a><i class="icon-angle-right"></i></li>';
		$form .= '</ul>';
		$form .= '<!-- END PAGE TITLE & BREADCRUMB-->';
		return $form;
	}
}

if ( ! function_exists('bs_searchbtn'))
{
	/*
	 *  bs_searchbtn('demo/search');
	 */
	function bs_searchbtn()
	{
		$form  = '<div class="form-actions fluid">';
		$form .= '<div class="col-md-offset-10 col-md-1">';
		$form .= '  <button type="submit" class="btn green">查询</button>';
		$form .= '</div></div>';
		return $form;
	}
	/*
	 * 					<div class="form-actions fluid">
						<div class="col-md-offset-10 col-md-1">
							<button type="submit" class="btn green">查询</button>
						</div>
					    </div>
	 */
}


if(!function_exists('bs_searchmodal'))
{
	function bs_searchmodal($url,$define)
	{
		$form = '<div class="portlet-body form" >';
		$form .= '<form action="'._judgeurl($url).'" id="modelfrom" method="post" class="form-horizontal ">';
		$form .= '<div class="form-body">';
		for ($i=0;$i<$define['init']['columns'];$i++){
			$form .= '<div class="row">';
			foreach ($define['items'] as $arr)
			if (isset($arr['column']) && $arr['column'] == $i){
				if ($arr['type'] == 'date') $form .= bs_form_date($arr);
				if ($arr['type'] == 'datetime') $form .= bs_form_datetime($arr);
				if ($arr['type'] == 'dateSE') $form .= bs_form_datese($arr);
				if ($arr['type'] == 'select') $form .= bs_form_select($arr);
				if ($arr['type'] == 'text') $form .= bs_form_text($arr);
			}
			$form .= '</div>';
		}
		$form .= '</div></form></div>';
		return $form;
	}
}

if ( ! function_exists('bs_searcharea'))
{
	/*
	 *  bs_searcharea($searchdefine);  define in $menudefine['searchdefine'];
	 */
	function bs_searcharea($url,$define)
	{
		$form  = '<!-- BEGIN  SEARCH BODY -->';
		$form .= '<div class="portlet-body form" >';
		$form .= '<form action="'._judgeurl($url).'" method="post" class="form-horizontal ">';
		$form .= '<div class="form-body">';
		for ($i=0;$i<$define['init']['columns'];$i++){
			$form .= '  <div class="row">';
			foreach ($define['items'] as $arr)
			if (isset($arr['column']) && $arr['column'] == $i){
				if ($arr['type'] == 'date') $form .= bs_form_date($arr);
				if ($arr['type'] == 'datetime') $form .= bs_form_datetime($arr);
				if ($arr['type'] == 'dateSE') $form .= bs_form_datese($arr);
				if ($arr['type'] == 'select') $form .= bs_form_select($arr);
				if ($arr['type'] == 'text') $form .= bs_form_text($arr);
			}
			$form .= '  </div>';
		}
		
		$form .= bs_searchbtn().'</div>';
		$form .= '</form></div>';
		$form .= '<!-- END  SEARCH BODY -->';
		return $form;
	}
	/*
		<!-- BEGIN  SEARCH BODY -->
		<div class="portlet-body form" >
		<form action="<?php echo site_url($url_model.'/'.$url_method.'/search');?>" method="post" class="form-horizontal ">
		<div class="form-body">
		<div class="row">
			<?php echo bs_form_date('date','日期选择','4,6,6');?>
		    <?php echo bs_form_datese('date','日期范围','4,6,6');?>
		    <?php echo bs_form_datetime('date','时间日期范围','4,6,6');?>
		</div>
		<div class="row">
			<?php echo bs_form_select('sel','下拉框',array('选择1','选择2'),'4,6,6');?>
			<?php echo bs_form_text('sel','文本框','4,6,6');?>
		</div><?php echo bs_searchbtn();?></div>
		</form>
		</div>
		<!-- END  SEARCH BODY -->
	 */
}
if ( ! function_exists('bs_searchbar'))
{
	/*
	 *  bs_searchbar('高级查询',
	 *  	array(
	 *  		'btnAdd'=> array(
	 *  			'id'   => 'addData'
	 *  			'name' => '添加',
	 *  			'url'  => 'demo/create',
	 *  			'icon' => 'icon-pencil',
	 *  			'color'=> 'blue',
	 *  			'isGroup' => true,
	 *  			'isCheck' => true,
	 *  		)
	 *  	),
	 *  	array(
	 *  		'btnPDF'=>array(
	 *  			'id'   => 'exportPDF'
	 *  			'name' => '导出PDF',
	 *  			'url'  => 'demo/exportpdf',
	 *  			'icon' => 'icon-tasks',
	 *  		)
	 *  	)
	 *  );
	 *  
	 *  
	 *  
	 *  
	 */
	function bs_searchbar($leftaction=array(),$rightaction=array(),$otheraction=array())
	{
		$CI =& get_instance();
		$form = '<!-- BEGIN SEARCH AREA HEADER-->
				 <div class="portlet-title">
				 <div class="caption"><i class="icon-search"></i>高级查询&nbsp&nbsp</div>
		 		 <div class="actions pull-left">';
		foreach ($leftaction as $arr){
			$form .= '    <a href="'._judgeurl($arr['url']).'" class="btn '.$arr['color'];
			// 点击按钮前是否确认.
			if (isset($arr['isCheck']) && $arr['isCheck'])
				$form .= ' os_check ';
			$form .= '" id="'.$arr['id'].'" disabled="disabled"';
			if (isset($arr['checkMsg']) && $arr['checkMsg']) {
				$form .= ' msg="'.$arr['checkMsg'].'" ';
			}
			$form .= ' >';
			if (isset($arr['icon'])) $form .= '<i class="'.$arr['icon'].'"></i>';
			$form .= $arr['name'].'</a>';
		}
		$form .= '</div>';
		$form .= '<div class="actions pull-right">';
		foreach ($rightaction as $arr){
			$form .= '    <a href="'._judgeurl($arr['url']).'" class="btn '.$arr['color'];
			// 点击按钮前是否确认.
			if (isset($arr['isCheck']) && $arr['isCheck']) {
				$form .= ' os_check ';
			}
			$form .= '" id="'.$arr['id'].'" disabled="disabled"';
			if (isset($arr['checkMsg']) && $arr['checkMsg']) {
				$form .= ' msg="'.$arr['checkMsg'].'" ';
			}
			$form .= ' >';
			if (isset($arr['icon'])) $form .= '<i class="'.$arr['icon'].'"></i>';
			$form .= $arr['name'].'</a>';
		}
		if (sizeof($otheraction)>0){
			$form .= '    <div class="btn-group"><a class="btn green" href="#" data-toggle="dropdown">';
			$form .= '      <i class="icon-cogs"></i> 其他 <i class="icon-angle-down"></i></a>';
			$form .= '      <ul class="dropdown-menu pull-right">';
			foreach ($otheraction as $arr){
				$form .= '        <li><a id="'.$arr['id'].'" href="'.$arr['url'].'"><i class="'.$arr['icon'].'"></i>'.$arr['name'].'</a></li>';
			}
			$form .= '      </ul>';
			$form .= '    </div>';
		}
		$form .= '  </div>';
		$form .= '  <div class="tools pull-right">';
		$form .= '  <a href="javascript:;" class="collapse"></a></div></div>';
		$form .= '<!-- END SEARCH AREA HEADER-->';
		return $form;
	}
	/*
	      <div class="portlet box light-grey">
		  <div class="portlet-title">
			 <div class="caption"><i class="icon-globe"></i>数据列表</div>
			     <div class="actions">
				<a href="javascript:void(0);" class="btn blue" id="addData"><i class="icon-plus-sign"></i>  添加</a>
				<a href="javascript:void(0);" class="btn blue group-control" id="modifyData"><i class="icon-pencil"></i>  修改</a>
				<a href="javascript:void(0);" class="btn blue group-control" id="deleteData"><i class="icon-trash"></i>  删除</a>
				<a href="javascript:void(0);" class="btn blue group-control" id="detailData"><i class="icon-book"></i>  明细</a>
				<div class="btn-group">
				   <a class="btn green" href="#" data-toggle="dropdown">
				   <i class="icon-cogs"></i> 其他
				   <i class="icon-angle-down"></i>
				   </a>
				   <ul class="dropdown-menu pull-right">
				      <li><a href="#"><i class="icon-tasks"></i> PDF</a></li>
				      <li><a href="#"><i class="icon-tasks"></i> EXCEL</a></li>
				      <li><a href="#"><i class="icon-print"></i> 打印</a></li>
				   </ul>
				</div>
			     </div>
			  </div>
	 */
}

if ( ! function_exists('bs_databar'))
{
	/*
	 *  bs_searchbar('高级查询',
	 *  	array(
	 *  		'btnAdd'=> array(
	 *  			'id'   => 'addData'
	 *  			'name' => '添加',
	 *  			'url'  => 'demo/create',
	 *  			'icon' => 'icon-pencil',
	 *  			'color'=> 'blue',
	 *  			'isGroup' => true,
	 *  			'isCheck' => true,
	 *  		)
	 *  	),
	 *  	array(
	 *  		'btnPDF'=>array(
	 *  			'id'   => 'exportPDF'
	 *  			'name' => '导出PDF',
	 *  			'url'  => 'demo/exportpdf',
	 *  			'icon' => 'icon-tasks',
	 *  		)
	 *  	)
	 *  );
	 */
	function bs_databar($action=array(),$otheraction=array())
	{
		$CI =& get_instance();
		$form = '<div class="portlet-title">';
		$form .= '  <div class="caption"><i class="icon-globe"></i>数据列表</div>';
		$form .= '  <div class="actions pull-rights">';
		foreach ($action as $arr){
			$form .= '    <a href="'._judgeurl($arr['url']).'" class="btn '.$arr['color'];
			// 点击按钮前是否确认.
			if (isset($arr['isCheck']) && $arr['isCheck'])
				$form .= ' os_check ';
			// 选中记录才显示
			$form .= '" id="'.$arr['id'].'">';
			if (isset($arr['icon'])) $form .= '<i class="'.$arr['icon'].'"></i>';
			$form .= $arr['name'].'</a>';
		}
		if (sizeof($otheraction)>0){
			$form .= '    <div class="btn-group"><a class="btn green" href="#" data-toggle="dropdown">';
			$form .= '      <i class="icon-cogs"></i> 其他 <i class="icon-angle-down"></i></a>';
			$form .= '      <ul class="dropdown-menu pull-right">';
			foreach ($otheraction as $arr){
				$form .= '        <li><a id="'.$arr['id'].'" href="'.$arr['url'].'"><i class="'.$arr['icon'].'"></i>'.$arr['name'].'</a></li>';
			}
			$form .= '      </ul>';
			$form .= '    </div>';
		}
		$form .= '  </div></div>';
		return $form;
	}
	
	/*
	   <div class="portlet-title">
		 <div class="caption"><i class="icon-globe"></i>数据列表</div>
		 <div class="actions">
			<a href="javascript:void(0);" class="btn blue" id="addData"><i class="icon-plus-sign"></i>  添加</a>
			<a href="javascript:void(0);" class="btn blue group-control" id="modifyData"><i class="icon-pencil"></i>  修改</a>
			<a href="javascript:void(0);" class="btn blue group-control" id="deleteData"><i class="icon-trash"></i>  删除</a>
			<a href="javascript:void(0);" class="btn blue group-control" id="detailData"><i class="icon-book"></i>  明细</a>
			<div class="btn-group">
			   <a class="btn green" href="#" data-toggle="dropdown">
			   <i class="icon-cogs"></i> 其他
			   <i class="icon-angle-down"></i>
			   </a>
			   <ul class="dropdown-menu pull-right">
			      <li><a href="#"><i class="icon-tasks"></i> PDF</a></li>
			      <li><a href="#"><i class="icon-tasks"></i> EXCEL</a></li>
			      <li><a href="#"><i class="icon-print"></i> 打印</a></li>
			   </ul>
			</div>
		  </div>
		</div>
	 */
}

if ( ! function_exists('bs_databarmodal'))
{
	function bs_databarmodal($action=array(),$otheraction=array())
	{
		$CI =& get_instance();
		$form  = '<div class="portlet-title">';
		$form .= '  <div class="caption"><i class="icon-globe"></i>数据列表</div>';
		$form .= '  <div class="actions pull-rights">';
		$form .= '	<a href="javascript:void(0);" class="btn blue group-control" id="success_modal"><i class="icon-pencil"></i>确定</a>';
		$form .= '    </div>';
		$form .= '  </div></div>';
		return $form;
	}
}
	function _judgeurl($url){
		$CI =& get_instance();
		$str = $CI->uri->uri_string();
		$arr = explode('/', $str);
		if (sizeof($arr)>2) $str = $arr[0]."/".$arr[1];
		return site_url().'/'.$str.'/'.$url;
	}

/* End of file tools_helper.php */
/* Location: ./application/helpers/boostrap/tools_helper.php */
