<?php

//TODO: refactor this (this is basically a copy of index.php)

/**
 * https://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
 * @param string $input
 * @return string
 */
function camelCaseToWords(string $input): string {
    preg_match_all('/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/', $input, $matches);
    return implode(' ', $matches[0]);
}

$filePath = rawurldecode($_GET['detail']);
$fileName = substr($filePath, strrpos($filePath, '/') + 1);
$fileContents = file_get_contents($filePath);
$youtubeId = $lister->getYoutubeId($fileName);
$youtubeLink = $lister->getYoutubeLink($youtubeId);
$youtubeEmbed = $lister->getYoutubeEmbed($youtubeId);
$difficulty = $lister->getDifficulty($fileName);

?>

<!DOCTYPE html>

<html>

    <head>

        <title>Directory listing of <?php echo $lister->getListedPath(); ?></title>
        <link rel="shortcut icon" href="<?php echo THEMEPATH; ?>/img/folder.png">

        <!-- STYLES -->
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo THEMEPATH; ?>/css/style.css">

        <!-- SCRIPTS -->
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo THEMEPATH; ?>/js/directorylister.js"></script>

        <!-- FONTS -->
        <link rel="stylesheet" type="text/css"  href="//fonts.googleapis.com/css?family=Cutive+Mono">

        <!-- META -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">

        <?php file_exists('analytics.inc') ? include('analytics.inc') : false; ?>

    </head>

    <body>

        <div id="page-navbar" class="navbar navbar-default navbar-fixed-top">
            <div class="container">

                <?php $breadcrumbs = $lister->listBreadcrumbs(); ?>

                <p class="navbar-text">
                    <?php foreach($breadcrumbs as $breadcrumb): ?>
                        <?php if ($breadcrumb != end($breadcrumbs)): ?>
                                <a href="<?php echo $breadcrumb['link']; ?>"><?php echo $breadcrumb['text']; ?></a>
                                <span class="divider">/</span>
                        <?php else: ?>
                            <?php echo $breadcrumb['text']; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </p>

                <div class="navbar-right">

                    <ul id="page-top-nav" class="nav navbar-nav">
                        <li>
                            <a href="javascript:void(0)" id="page-top-link">
                                <i class="fa fa-arrow-circle-up fa-lg"></i>
                            </a>
                        </li>
                    </ul>

                    <?php  if ($lister->isZipEnabled()): ?>
                        <ul id="page-top-download-all" class="nav navbar-nav">
                            <li>
                                <a href="?zip=<?php echo $lister->getDirectoryPath(); ?>" id="download-all-link">
                                    <i class="fa fa-download fa-lg"></i>
                                </a>
                            </li>
                        </ul>
                    <?php endif; ?>

                </div>

            </div>
        </div>

        <div id="page-content" class="container">

            <?php file_exists('header.php') ? include('header.php') : include($lister->getThemePath(true) . "/default_header.php"); ?>

            <?php if($lister->getSystemMessages()): ?>
                <?php foreach ($lister->getSystemMessages() as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?>">
                        <?php echo $message['text']; ?>
                        <a class="close" data-dismiss="alert" href="#">&times;</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4 hidden-sm hidden-xs">
                    <div id="directory-list-header">
                        <div>
                            <div>Title / File</div>
                        </div>
                    </div>

                    <ul id="directory-listing" class="nav nav-pills nav-stacked">

                        <?php foreach($dirArray as $name => $fileInfo): ?>
                            <li data-name="<?php echo $name; ?>" data-href="<?php echo $fileInfo['url_path']; ?>">
                                <a href="<?php echo $fileInfo['url_path']; ?>" class="clearfix" data-name="<?php echo $name; ?>">
                                    <div class="row">
                                        <span class="file-name col-xs-12">
                                            <i class="fa <?php echo $fileInfo['icon_class']; ?> fa-fw"></i>
                                            <?php echo
                                            camelCaseToWords(preg_replace('/\-/', ' - ',
                                                preg_replace('/(__diff_[\d]|__yt_[^_\.]+|\.txt)/', '', $name)
                                            ));
                                            ?>
                                        </span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                </div>
                <div class="col-md-8 col-sm-12 col-xs-12">
                    <h1><?= camelCaseToWords(preg_replace('/\-/', ' - ',
                            preg_replace('/(__diff_[\d]|__yt_[^_\.]+|\.txt)/', '', $fileName)
                        )); ?></h1>
                    <div class="pull-right">
                        <span class="file-difficulty">
                            <?php if ($difficulty > 0) { ?>
                                <i class="
                                    fa fa-battery-<?= $difficulty - 1 ?>
                                    text-<?php
                                if ($difficulty === 5) {
                                    echo 'danger';
                                } else if ($difficulty === 4) {
                                    echo 'warning';
                                } else if ($difficulty === 1) {
                                    echo 'success';
                                }
                                ?>
                                "></i>
                            <?php } ?>
                        </span>
                    </div>
                    <div class="file-youtube">
                        <?php if ($youtubeEmbed !== '') {
                            echo $youtubeEmbed;
                        } ?>
                    </div>
                    <div class="content">
                        <pre><code><?= $fileContents ?></code></pre>
                    </div>
                </div>
            </div>
        </div>

        <?php file_exists('footer.php') ? include('footer.php') : include($lister->getThemePath(true) . "/default_footer.php"); ?>

        <div id="file-info-modal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">{{modal_header}}</h4>
                    </div>

                    <div class="modal-body">

                        <table id="file-info" class="table table-bordered">
                            <tbody>

                                <tr>
                                    <td class="table-title">MD5</td>
                                    <td class="md5-hash">{{md5_sum}}</td>
                                </tr>

                                <tr>
                                    <td class="table-title">SHA1</td>
                                    <td class="sha1-hash">{{sha1_sum}}</td>
                                </tr>

                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>

    </body>

</html>
