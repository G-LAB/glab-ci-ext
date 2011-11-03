<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['base_url'] = current_url().'?paginate=true';

$config['page_query_string'] = TRUE;
$config['query_string_segment'] = 'offset';

$config['full_tag_open'] = '<span class="pagination">';
$config['full_tag_close'] = '</span>';

$config['next_link'] = '&raquo;';
$config['prev_link'] = '&laquo;';

/* End of file pagination.php */
/* Location: ./system/application/config/pagination.php */ 