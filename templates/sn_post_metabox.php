<table> 
<?php foreach($this->_meta as $field => $data) : ?>
    <tr valign="top">
        <th class="metabox_label_column"><label for="<?php echo $field; ?>"><?php echo _e($data['label'], $field); ?></label></th>
        <td>
        <?php if($data['widget'] == 'text') : ?>
            <input type="text" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo get_post_meta($post->ID, $field, true); ?>" />
        <?php elseif($data['widget'] == 'textarea') : ?>
            <textarea id="<?php echo $field; ?>" name="<?php echo $field; ?>" rows="8" cols="40"><?php echo get_post_meta($post->ID, $field, true); ?></textarea>
        <?php endif; ?>	
        <?php if(!empty($data['help_text'])) : ?>
            <p class="help"><?php echo $data['help_text']; ?></p>
        <?php endif; ?>
        </td>
    <tr>
<?php endforeach; ?>
</table>