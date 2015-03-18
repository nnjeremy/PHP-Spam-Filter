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
		'setSeen'	=> true	),
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

// a function to decode MIME message header extensions and get the text
function decode_imap_text($str){
    $result = '';
    $decode_header = imap_mime_header_decode($str);
    foreach ($decode_header AS $obj) {
        $result .= htmlspecialchars(rtrim($obj->text, "\t"));
	}
    return $result;
};

// Start script
foreach ($mailboxes as $current_mailbox) {
	if ($current_mailbox['enable']) {
		// Open stream
		$stream = imap_open($current_mailbox['server'].$current_mailbox['inboxFolder'], $current_mailbox['username'], $current_mailbox['password']);
                $streamSpam = imap_open($current_mailbox['server'].$current_mailbox['spamFolder'], $current_mailbox['username'], $current_mailbox['password']);

		if ($stream) {
			// Get our messages from the last day
			// Instead of searching for this day's message you could search for all the messages in your inbox using: $emails = imap_search($stream,'ALL');
			$emails = imap_search($stream, 'SINCE '. date('d-M-Y',strtotime("-1 day")));

			if (count($emails)){
				// If we've got some email IDs, sort them from new to old and show them
				rsort($emails);

				foreach($emails as $email_id){
					// Fetch the email's overview and show subject, from and date.
					$overview = imap_fetch_overview($stream,$email_id,0);

					if(imap_search($streamSpam, 'FROM '.decode_imap_text($overview[0]->from))) {
						if($current_mailbox['setSeen']) {
							// Set mail seen
							imap_setflag_full($stream,$email_id, "\\Seen");
						}

						// Move email
						imap_mail_move($stream,$email_id,$current_mailbox['spamFolder']);
					}
				}
			}

		// Close stream
		imap_close($streamSpam);
		imap_close($stream, CL_EXPUNGE);
		}
	}
} // end foreach
?>
