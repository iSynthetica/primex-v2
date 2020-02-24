<?php
/**
 * Contacts Template Content
 *
 * @package Hooka/Parts/Content
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$warehouse = snth_get_locations( 'warehouse' );
$partners = snth_get_locations( 'vendor' );

$our_company = get_field('our_company');
$our_partners = get_field('our_partners');

$partners_first_column = array();
$partners_second_column = array();

foreach ($partners as $partner) {
    $columnn = wp_get_post_terms($partner->ID, 'partner_column');

    if ( 'first_column' == $columnn[0]->slug ) {
        $partners_first_column[] = $partner;
    } else {
        $partners_second_column[] = $partner;
    }

    // var_dump($partners_first_column);
}
?>

<section class="page-section pt-10 pb-10" id="partners-section">
    <div class="container relative">
        <div class="row">
            <div class="col-sm-4">
                <div class="row">
                    <?php
                    if (!empty($warehouse)) {
                        ?>
                        <div class="col-xs-12">
                            <h3 class="font-alt mb-40 mb-sm-20">
                                <?php echo $our_company['title'] ?>
                            </h3>
                        </div>
                        <?php
                        foreach ($warehouse as $partner) {
                            ?>
                            <div class="col-xs-12 pb-20 pb-xs-10">
                                <h4 class="font-alt"><?php echo $partner->post_title; ?></h4>
                                <?php
                                $country = get_field('country', $partner->ID);
                                $city = get_field('city', $partner->ID);
                                $address = get_field('address', $partner->ID);
                                $warehouse_post = get_post($partner->ID);
                                $content = $warehouse_post->post_content;

                                if (!empty($content)) {
                                    ?>
                                    <div class="partner-contact-section">
                                        <?php echo wpautop($content); ?>
                                    </div>
                                    <?php
                                } else {
                                    if (!empty($country) || !empty($city) || !empty($city)) {
                                        ?>
                                        <div class="partner-contact-section">
                                            <p><?php echo __('Address', 'snthwp') ?>:
                                                <?php echo !empty($country) ? $country . ', ' : ''; ?>
                                                <?php echo !empty($city) ? $city . ', ' : ''; ?>
                                                <?php echo !empty($address) ? $address : ''; ?>
                                            </p>
                                        </div>
                                        <?php
                                    }

                                    $phone = get_field('phone', $partner->ID);

                                    if (!empty($phone)) {
                                        ?>
                                        <div class="partner-contact-section">
                                            <p>
                                                <?php echo __('Phone', 'snthwp') ?>:
                                                <?php echo $phone; ?>
                                            </p>
                                        </div>
                                        <?php
                                    }

                                    $site = get_field('site', $partner->ID);

                                    if (!empty($site)) {
                                        ?>
                                        <div class="partner-contact-section">
                                            <p><?php echo __('Site', 'snthwp') ?>:
                                                <a href="<?php echo $site; ?>"><?php echo $site; ?></a>
                                            </p>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="row multi-columns-row">
                    <?php
                    if(!empty($partners)) {
                        ?>
                        <div class="col-xs-12">
                            <h3 class="font-alt mb-40 mb-sm-20">
                                <?php echo $our_partners['title'] ?>
                            </h3>
                        </div>

                        <div class="col-xs-12">
                            <div class="row">
                                <div class="col-sm-6">
                                    <?php
                                    foreach ($partners_first_column as $partner) {
                                        ?>
                                        <div class="pb-20 pb-xs-10">
                                            <h4 class="font-alt"><?php echo $partner->post_title; ?></h4>
                                            <?php
                                            $country = get_field('country', $partner->ID);
                                            $city = get_field('city', $partner->ID);
                                            $address = get_field('address', $partner->ID);

                                            if (!empty($country) || !empty($city) || !empty($city)) {
                                                ?>
                                                <div class="partner-contact-section">
                                                    <p><?php echo __('Address', 'snthwp') ?>:
                                                        <?php echo !empty($country) ? $country . ', ' : ''; ?>
                                                        <?php echo !empty($city) ? $city . ', ' : ''; ?>
                                                        <?php echo !empty($address) ? $address : ''; ?>
                                                    </p>
                                                </div>
                                                <?php
                                            }

                                            $phone = get_field('phone', $partner->ID);

                                            if (!empty($phone)) {
                                                ?>
                                                <div class="partner-contact-section">
                                                    <p>
                                                        <?php echo __('Phone', 'snthwp') ?>:
                                                        <?php echo $phone; ?>
                                                    </p>
                                                </div>
                                                <?php
                                            }

                                            $site = get_field('site', $partner->ID);

                                            if (!empty($site)) {
                                                ?>
                                                <div class="partner-contact-section">
                                                    <p><?php echo __('Site', 'snthwp') ?>:
                                                        <a href="<?php echo $site; ?>"><?php echo $site; ?></a>
                                                    </p>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-sm-6">
                                    <?php
                                    foreach ($partners_second_column as $partner) {
                                        ?>
                                        <div class="pb-20 pb-xs-10">
                                            <h4 class="font-alt"><?php echo $partner->post_title; ?></h4>
                                            <?php
                                            $country = get_field('country', $partner->ID);
                                            $city = get_field('city', $partner->ID);
                                            $address = get_field('address', $partner->ID);

                                            if (!empty($country) || !empty($city) || !empty($city)) {
                                                ?>
                                                <div class="partner-contact-section">
                                                    <p><?php echo __('Address', 'snthwp') ?>:
                                                        <?php echo !empty($country) ? $country . ', ' : ''; ?>
                                                        <?php echo !empty($city) ? $city . ', ' : ''; ?>
                                                        <?php echo !empty($address) ? $address : ''; ?>
                                                    </p>
                                                </div>
                                                <?php
                                            }

                                            $phone = get_field('phone', $partner->ID);

                                            if (!empty($phone)) {
                                                ?>
                                                <div class="partner-contact-section">
                                                    <p>
                                                        <?php echo __('Phone', 'snthwp') ?>:
                                                        <?php echo $phone; ?>
                                                    </p>
                                                </div>
                                                <?php
                                            }

                                            $site = get_field('site', $partner->ID);

                                            if (!empty($site)) {
                                                ?>
                                                <div class="partner-contact-section">
                                                    <p><?php echo __('Site', 'snthwp') ?>:
                                                        <a href="<?php echo $site; ?>"><?php echo $site; ?></a>
                                                    </p>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
