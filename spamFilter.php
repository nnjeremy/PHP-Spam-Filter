<?php
// configure your imap mailboxes
$mailboxes = array(
	array(
                'enable'        => true,
		'server' 	=> '{imap.yourdomain.com:993/imap/ssl}',
		'inboxFolder' 	=> 'INBOX',
		'spamFolder' 	=> 'INBOX.Spam',
		'username' 	=> 'info@yourdomain.com',
		'password' 	=> 'yourpassword',
		'setSeen'	=> true
	),
	array(
                'enable'        => true,
		'server' 	=> '{imap.yourdomain.com:993/imap/ssl}',
		'inboxFolder' 	=> 'INBOX',
		'spamFolder' 	=> 'INBOX.Spam',
		'username' 	=> 'info@yourdomain.com',
		'password' 	=> 'yourpassword',
		'setSeen'	=> true
	)
);

// Start script
foreach ($mailboxes as $current_mailbox) {
	// Check enable option
	if ($current_mailbox['enable']) {
		// Open stream
		$streamInbox = imap_open($current_mailbox['server'].$current_mailbox['inboxFolder'], $current_mailbox['username'], $current_mailbox['password']);
                $streamSpam = imap_open($current_mailbox['server'].$current_mailbox['spamFolder'], $current_mailbox['username'], $current_mailbox['password']);

		if ($streamInbox && $streamSpam) {
			// Get our messages from the last day
			// Instead of searching for this day's message you could search for all the messages in your inbox using: $emails = imap_search($streamInbox,'ALL');
			$emails = imap_search($streamInbox, 'SINCE '. date('d-M-Y',strtotime("-1 day")));

			if (count($emails)){
				// If we've got some email IDs, sort them from new to old and show them
				//rsort($emails);

				foreach($emails as $email_id){
					// Fetch the email's overview and show subject, from and date.
					$inboxMailheader = imap_headerinfo($streamInbox,$email_id);
					$inboxFromMailAdresse = $inboxMailheader->from[0]->mailbox . "@" . $inboxMailheader->from[0]->host;

					// Search adresse in the spam folder
					if(imap_search($streamSpam, 'FROM '.$inboxFromMailAdresse)) {
						// Check seen option
						if($current_mailbox['setSeen']) {
							// Set mail seen
							imap_setflag_full($streamInbox,$email_id, "\\Seen");
						}

						// Move email
						imap_mail_move($streamInbox,$email_id,$current_mailbox['spamFolder']);
					}
				}
			}

		// Close stream
		imap_close($streamSpam);
		imap_close($streamInbox, CL_EXPUNGE);
		}
	}
} // end foreach
?>
