<?=form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=freeform_sms');?>

<?php
ee()->table->set_template($cp_pad_table_template);
ee()->table->set_heading(
    array('data' => lang('form'), 'style' => 'width:15%'),
    array('data' => lang('enabled'), 'style' => 'width:5%;'),
    array('data' => lang('notify'), 'style' => 'width:40%'),
    array('data' => lang('message'), 'style' => 'width:40%')
);

foreach ($settings as $id => $data)
{
    ee()->table->add_row(
        $data['form_label'],
        form_checkbox('settings['.$id.'][enabled]', 'y', ($data['enabled'] == 'y' ? true : false)),
        form_input('settings['.$id.'][notify_email]', $data['notify_email']),
        form_textarea('settings['.$id.'][notify_message]', $data['notify_message'])
    );
}

echo ee()->table->generate();

?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>

<?php ee()->table->clear()?>
<?=form_close()?>

<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/freeform_sms/views/index.php */