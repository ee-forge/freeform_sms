<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Freeform SMS Extension Class for ExpressionEngine 2
 *
 * @package     ExpressionEngine
 * @subpackage  Freeform SMS
 * @category    Extensions
 * @author      Ron Hickson
 * @link        http://ee-forge.com/
 */

class Freeform_sms_ext {

    var $name             = 'Freeform SMS';
    var $version          = '0.15';
    var $description      = 'Adds an SMS message to selected Freeform forms.';
    var $settings_exist   = 'y';
    var $docs_url         = '';
    var $settings         = array();

    function __construct($settings = '')
    {
        $this->EE =& get_instance();
        $this->settings = $settings;
    }

    /**
     * Settings
     *
     * This function returns the settings for the extensions
     *
     * @param   Array   settings
     * @return  void
     */
    function settings_form($current)
    {
        ee()->load->helper('form');
        ee()->load->library('table');

        $vars = array();

        // Get all existing forms
        $forms = ee()->db->select('form_name, form_label, form_id')->get('freeform_forms');
        if($forms->num_rows() > 0) {
            // We have an array of settings to work with
            foreach($forms->result() as $form) {
                if(is_array($current)) {
                    foreach ($current as $k => $v) {
                        if ($form->form_id == $k) {
                            $vars['settings'][$form->form_id]['form_name'] = $form->form_name;
                            $vars['settings'][$form->form_id]['form_label'] = $form->form_label;
                            $vars['settings'][$form->form_id]['enabled'] = isset($v['enabled']) ? 'y' : 'n';
                            $vars['settings'][$form->form_id]['notify_email'] = $v['notify_email'];
                            $vars['settings'][$form->form_id]['notify_message'] = $v['notify_message'];
                            // Found settings so unset and break out of the current foreach loop
                            unset($current[$k]);
                            break;
                        } else {
                            $vars['settings'][$form->form_id]['form_name'] = $form->form_name;
                            $vars['settings'][$form->form_id]['form_label'] = $form->form_label;
                            $vars['settings'][$form->form_id]['enabled'] = 'n';
                            $vars['settings'][$form->form_id]['notify_email'] = '';
                            $vars['settings'][$form->form_id]['notify_message'] = '';
                        }
                    }
                } else {
                    $vars['settings'][$form->form_id]['form_name'] = $form->form_name;
                    $vars['settings'][$form->form_id]['form_label'] = $form->form_label;
                    $vars['settings'][$form->form_id]['enabled'] = 'n';
                    $vars['settings'][$form->form_id]['notify_email'] = '';
                    $vars['settings'][$form->form_id]['notify_message'] = '';
                }
            }
        }

        return ee()->load->view('index', $vars, true);
    }

    /**
     * Save Settings
     *
     * This function saves the settings for the extension
     *
     * @return  void
     */
    function save_settings() {

        unset($_POST['submit']);

        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->update('extensions', array('settings' => serialize($_POST['settings'])));

        $this->EE->session->set_flashdata(
            'message_success',
            lang('preferences_updated')
        );

    }

    /**
     * Activate Extension
     *
     * This function enters the extension into the exp_extensions table
     *
     * @see http://codeigniter.com/user_guide/database/index.html for
     * more information on the db class.
     *
     * @return void
     */
    function activate_extension()
    {
        $data = array(
            'class'       => __CLASS__,
            'hook'        => 'freeform_module_admin_notification',
            'method'      => 'send_message',
            'settings'    => serialize($this->settings),
            'priority'    => 10,
            'version'     => $this->version,
            'enabled'     => 'y'
        );

        // insert in database
        $this->EE->db->insert('extensions', $data);
    }


    /**
     * Update Extension
     *
     * This function performs any necessary db updates when the extension
     * page is visited
     *
     * @return 	mixed	void on update / false if none
     */
    function update_extension($current = '')
    {
        if ($current == '' || $current == $this->version)
        {
            return FALSE;
        }

        if ($current < '0.1')
        {
            // Update to version 1.0
        }

        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->update(
            'extensions',
            array('version' => $this->version)
        );
    }


    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    function disable_extension()
    {
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('extensions');
    }

    /**
     * Send SMS message
     *
     * @return  void
     */
    function send_message($fields, $entry_id, $vars, $form_id, $obj) {

        // Load the helpers we need
        ee()->load->library('email');
        ee()->load->helper('text');
        ee()->load->library('template');

        $form = $this->settings[$form_id];
        // Form SMS is enabled so send SMS
        if(isset($form['enabled'])) {

            $inputs[] = $vars['field_inputs'];
            $sms = ee()->TMPL->parse_variables($form['notify_message'], $inputs);

            ee()->email->mailtype = "text";
            ee()->email->from(ee()->config->item('webmaster_email'));
            ee()->email->to($form['notify_email']);
            ee()->email->subject($vars['form_label']);
            ee()->email->message(entities_to_ascii($sms));
            ee()->email->Send();
        }

        return $vars;
    }

}

/* End of file ext.honeepot.php */
/* Location: ./system/expressionengine/third_party/freeform_sms/ext.freeform_sms.php */