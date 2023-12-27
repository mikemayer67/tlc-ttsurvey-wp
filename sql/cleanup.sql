delete from wp_postmeta where post_id in (SELECT id FROM wp_sandbox.wp_posts where post_type='tlc-ttsurvey-id');
delete from wp_posts where post_type='tlc-ttsurvey-id';