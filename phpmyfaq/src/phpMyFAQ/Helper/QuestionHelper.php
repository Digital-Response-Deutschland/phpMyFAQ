<?php

namespace phpMyFAQ\Helper;

/**
 * Questions helper class for phpMyFAQ.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/.
 *
 * @package   phpMyFAQ\Helper
 * @author    Thorsten Rinne <thorsten@phpmyfaq.de>
 * @copyright 2019 phpMyFAQ Team
 * @license   http://www.mozilla.org/MPL/2.0/ Mozilla Public License Version 2.0
 * @link      https://www.phpmyfaq.de
 * @since     2019-11-26
 */

use phpMyFAQ\Category;
use phpMyFAQ\Configuration;
use phpMyFAQ\Mail;
use phpMyFAQ\Question;
use phpMyFAQ\User;

/**
 * Class QuestionHelper
 * @package phpMyFAQ\Helper
 */
class QuestionHelper
{
    /** @var Configuration */
    private $config;

    /** @var Category */
    private $category;

    /**
     * QuestionHelper constructor.
     * @param Configuration $config
     * @param Category $category
     */
    public function __construct(Configuration $config, Category $category)
    {
        $this->config = $config;
        $this->category = $category;
    }

    /**
     * @param array $questionData
     * @param $categories
     */
    public function sendSuccessMail(array $questionData, $categories)
    {
        $questionObject = new Question($this->config);
        $questionObject->addQuestion($questionData);

        $questionMail = 'User: ' . $questionData['username'] .
            ', mailto:' . $questionData['email'] . "\n" . $PMF_LANG['msgCategory'] .
            ': ' . $categories[$questionData['category_id']]['name'] . "\n\n" .
            wordwrap($questionData['question'], 72) . "\n\n" .
            $this->config->getDefaultUrl() . 'admin/';

        $userId = $this->category->getOwner($questionData['category_id']);
        $oUser = new User($this->config);
        $oUser->getUserById($userId);

        $userEmail = $oUser->getUserData('email');
        $mainAdminEmail = $faqConfig->get('main.administrationMail');

        $mailer = new Mail($faqConfig);
        $mailer->setReplyTo($questionData['email'], $questionData['username']);
        $mailer->addTo($mainAdminEmail);
        // Let the category owner get a copy of the message
        if (!empty($userEmail) && $mainAdminEmail != $userEmail) {
            $mailer->addCc($userEmail);
        }
        $mailer->subject = '%sitename%';
        $mailer->message = $questionMail;
        $mailer->send();
        unset($mailer);
    }
}
