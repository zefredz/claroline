<!-- $Id$ -->
<select name="profileId">

    <?php foreach ( $this->profileList->getProfileList() as $profile ): ?>
        
        <?php 
        
        if ( $this->ignoreNonMemberProfiles 
            && ( $profile->name == 'Guest' || $profile->name == 'Anonymous' ) ):
            continue;
        endif; 
        
        ?>

        <option 
            value="<?php echo $profile->id; ?>"<?php echo $profile->name == 'User' ? ' selected="selected"' : ''; ?>>
            <?php echo get_lang($profile->name); ?>
        </option>

    <?php endforeach; ?>

</select>
