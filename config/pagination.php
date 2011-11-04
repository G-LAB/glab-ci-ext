<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['base_url'] = current_url().'?paginate=true';

$config['page_query_string'] = TRUE;
$config['query_string_segment'] = 'offset';

$config['full_tag_open'] = '<span class="pagination">';
$config['full_tag_close'] = '</span>';

$config['next_link'] = 'Next &gt;';
$config['prev_link'] = '&lt; Previous';

$config['last_link'] = 'Last &raquo;';
$config['first_link'] = '&laquo; First';

$config['cur_tag_open'] = '<span class="current">';
$config['cur_tag_close'] = '</span>';

/* End of file pagination.php */
/* Location: ./system/application/config/pagination.php */ 