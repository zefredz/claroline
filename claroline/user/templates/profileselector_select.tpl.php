<!-- $Id$ -->
<select name="profileId">

    <?php foreach ( $this->profileList->getProfileList() as $profile ): ?>

        <option 
            value="<?php echo $profile->id; ?>"<?php echo $profile->name == 'User' ? ' selected="selected"' : ''; ?>>
            <?php echo $profile->name; ?>
        </option>

    <?php endforeach; ?>

</select>
