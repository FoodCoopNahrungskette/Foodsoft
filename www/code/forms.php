<?

// functions to output one row of a form
//
// if a $fieldname is alread part of $self_fields (ie, part of the current view), the value
// will just be printed and cannot be modified (only applies to types that can be in $self_fields).
// the line will not be closed; so e.g. a submission_button() can be appended to the last row
//

function form_row_konto( $label = 'Konto:', $fieldname = 'konto_id', $initial = 0 ) {
  open_tr();
    open_td( 'label', '', $label );
    if( ( $konto_id = self_field( $fieldname ) ) === NULL )
      $konto_id = $initial;
    else
      $fieldname = false;
    open_td( 'kbd' ); echo konto_view( $konto_id, $fieldname );
}

function form_row_kontoauszug( $label = 'Kontoauszug:', $fieldname = 'auszug', $initial_jahr = 0, $initial_nr = 0 ) {
  open_tr();
    open_td( 'label', '', $label );
    $auszug_jahr = self_field( $fieldname.'_jahr' );
    $auszug_nr = self_field( $fieldname.'_nr' );
    if( $auszug_jahr !== NULL and $auszug_nr !== NULL )
      $fieldname = false;
    if( $auszug_jahr === NULL )
      $auszug_jahr = $initial_jahr;
    if( $auszug_nr === NULL )
      $auszug_nr = $initial_nr;
    open_td( 'kbd' ); echo kontoauszug_view( 0, $auszug_jahr, $auszug_nr, $fieldname );
}

function form_row_gruppe( $label = 'Gruppe:', $fieldname = 'gruppen_id', $initial = 0 ) {
  open_tr();
    open_td('label', '', $label );
    if( ( $gruppen_id = self_field( $fieldname ) ) === NULL )
      $gruppen_id = $initial;
    else
      $fieldname = false;
    open_td( 'kbd' ); echo gruppe_view( $gruppen_id, $fieldname );
}

function form_row_lieferant( $label = 'Lieferant:', $fieldname = 'lieferanten_id', $initial = 0 ) {
  open_tr();
    open_td('label', '', $label );
    if( ( $lieferant_id = self_field( $fieldname ) ) === NULL )
      $lieferant_id = $initial;
    else
      $fieldname = false;
    open_td( 'kbd' ); echo lieferant_view( $lieferant_id, $fieldname );
}

function form_row_date( $label, $fieldname, $initial = 0 ) {
  $year = self_field( $fieldname.'_year' );
  $month = self_field( $fieldname.'_month' );
  $day = self_field( $fieldname.'_day' );
  if( ($year !== NULL) and ($day !== NULL) and ($month !== NULL) ) {
    $date = "$year-$month-$day";
    $fieldname = false;
  } else {
    $date = $initial;
  }
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd' ); echo date_view( $date, $fieldname );
}

function form_row_date_time( $label, $fieldname, $initial = 0 ) {
  $year = self_field( $fieldname.'_year' );
  $month = self_field( $fieldname.'_month' );
  $day = self_field( $fieldname.'_day' );
  $hour = self_field( $fieldname.'_hour' );
  $minute = self_field( $fieldname.'_minute' );
  if( ($year !== NULL) and ($day !== NULL) and ($month !== NULL) and ($hour !== NULL) and ($minute !== NULL) ) {
    $datetime = "$year-$month-$day $hour:$minute";
    $fieldname = false;
  } else {
    $datetime = $initial;
  }
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd' ); echo date_time_view( $datetime, $fieldname );
}

function form_row_betrag( $label = 'Betrag:' , $fieldname = 'betrag', $initial = 0.0 ) {
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd' ); echo price_view( $initial, $fieldname );
}

function form_row_text( $label = 'Notiz:', $fieldname = 'notiz', $size = 60, $initial = '' ) {
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd' ); echo string_view( $initial, $size, $fieldname );
}



function form_finish_transaction( $transaction_id ) {
  $trans = sql_get_transaction( $transaction_id );
  open_form('', '', '', "action=finish_transaction,transaction_id=$transaction_id" );
    open_table('layout');
      form_row_konto();
      form_row_auszug();
      form_row_date( 'Valuta:', 'valuta' );
      open_tr();
        open_td( 'right', "colspan='2'" );
        echo "Best&auml;tigen: <input type='checkbox' name='confirm' value='yes'>";
        qquad();
        submission_button( 'OK' );
    close_table();
  close_form();
}

function action_finish_transaction() {
  global $transaction_id, $konto_id, $auszug_jahr, $auszug_nr, $valuta_day, $valuta_month, $valuta_year;
  global $dienstkontrollblatt_id;
  need_http_var( 'transaction_id', 'U' );
  need_http_var( 'auszug_jahr', 'U' );
  need_http_var( 'auszug_nr', 'U' );
  need_http_var( 'konto_id', 'U' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );

  fail_if_readonly();
  nur_fuer_dienst(4);

  $soll_id = -$transaction_id;
  $soll_transaction = sql_get_transaction( $soll_id );

  $haben_id = sql_bank_transaktion(
    $konto_id, $auszug_jahr, $auszug_nr
  , $soll_transaction['soll'], "$valuta_year-$valuta_month-$valuta_day"
  , $dienstkontrollblatt_id, $notiz, 0
  );

  sql_link_transaction( $soll_id, $haben_id );

  return sql_update( 'gruppen_transaktion', $transaction_id, array(
    'dienstkontrollblatt_id' => $dienstkontrollblatt_id
  ) );
}




function formular_buchung_gruppe_bank( $notiz_initial = 'Einzahlung' ) {
  open_form( 'small_form', '', '', 'action=buchung_gruppe_bank' );
    open_fieldset( 'small_form', '', 'Einzahlung / Auszahlung Gruppe' );
      open_table('layout');
        form_row_gruppe();
        form_row_konto();
        form_row_kontoauszug();
        form_row_date( 'Valuta:', 'valuta' );
        tr_title( 'Betrag: positiv bei Einzahlung, negativ bei Auszahlung' );
        form_row_betrag( 'Haben Konto:' );
        form_row_text( 'Notiz:', 'notiz', 60, $notiz_initial );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_buchung_gruppe_bank() {
  global $gruppen_id, $konto_id, $auszug_nr, $auszug_jahr, $valuta_day, $valuta_month, $valuta_year, $betrag, $notiz;
  global $specialgroups;
  $problems = false;

  need_http_var( 'betrag', 'f' );
  need_http_var( 'gruppen_id', 'U' );
  $gruppen_name = sql_gruppenname( $gruppen_id );
  if( $betrag < 0 ) {
    need_http_var( 'notiz', 'H' );
  } else {
    get_http_var( 'notiz', 'H', "Einzahlung Gruppe $gruppen_name" );
  }
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  need_http_var( 'konto_id', 'U' );
  need_http_var( 'auszug_jahr', 'U' );
  need_http_var( 'auszug_nr', 'U' );
  need( ! in_array( $gruppen_id, $specialgroups ) );
  need( sql_gruppenname( $gruppen_id ) );

  if( ! $problems ) {
    sql_doppelte_transaktion(
      array( 'konto_id' => -1, 'gruppen_id' => $gruppen_id )
    , array( 'konto_id' => $konto_id, 'auszug_nr' => "$auszug_nr", 'auszug_jahr' => "$auszug_jahr" )
    , $betrag
    , "$valuta_year-$valuta_month-$valuta_day"
    , "$notiz"
    );
  }
}


function formular_buchung_lieferant_bank( $notiz_initial = 'Abbuchung Lieferant' ) {
  open_form( 'small_form', '', '', 'action=buchung_lieferant_bank' );
    open_fieldset( '', '', 'Überweisung / Lastschrift Lieferant' );
      open_table('layout');
        form_row_lieferant();
        form_row_konto();
        form_row_kontoauszug();
        form_row_date( 'Valuta:', 'valuta' );
        tr_title( 'Betrag: positiv bei Einzahlung, negativ bei Auszahlung/Lastschrift' );
        form_row_betrag( 'Haben Konto:' );
        form_row_text( 'Notiz:', 'notiz', 60, $notiz_initial );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_buchung_lieferant_bank() {
  global $lieferanten_id, $konto_id, $auszug_jahr, $auszug_nr, $betrag, $notiz, $valuta_day, $valuta_month, $valuta_year;
  $problems = false;

  need_http_var( 'betrag', 'f' );
  need_http_var( 'lieferanten_id', 'U' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  need_http_var( 'konto_id', 'U' );
  need_http_var( 'auszug_jahr', 'U' );
  need_http_var( 'auszug_nr', 'U' );
  need_http_var( 'notiz', 'H' );
  sql_doppelte_transaktion(
    array( 'konto_id' => $konto_id, 'auszug_nr' => "$auszug_nr", 'auszug_jahr' => "$auszug_jahr" )
  , array( 'konto_id' => -1, 'lieferanten_id' => $lieferanten_id )
  , $betrag
  , "$valuta_year-$valuta_month-$valuta_day"
  , "$notiz"
  );
}

function formular_buchung_gruppe_lieferant( $notiz_initial = 'Zahlung an Lieferant' ) {
  open_form( 'small_form', '', '', 'action=buchung_gruppe_lieferant' );
    open_fieldset( 'small_form', '', 'Zahlung von Gruppe an Lieferant' );
      open_table('layout');
        form_row_gruppe();
        form_row_lieferant();
        form_row_date( 'Valuta:', 'valuta' );
        tr_title( 'Betrag: positiv: Zahlung an Lieferant / negativ: Zahlung an Gruppe' );
        form_row_betrag( 'Haben Lieferant:' );
        form_row_text( 'Notiz:', 'notiz', 60, $notiz_initial );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_buchung_gruppe_lieferant() {
  global $betrag, $lieferanten_id, $gruppen_id, $notiz, $valuta_day, $valuta_month, $valuta_year;
  need_http_var( 'betrag', 'f' );
  need_http_var( 'lieferanten_id', 'U' );
  need_http_var( 'gruppen_id', 'U' );
  need_http_var( 'notiz', 'H' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  sql_doppelte_transaktion(
    array( 'konto_id' => -1, 'gruppen_id' => $gruppen_id )
  , array( 'konto_id' => -1, 'lieferanten_id' => $lieferanten_id )
  , $betrag
  , "$valuta_year-$valuta_month-$valuta_day"
  , "$notiz"
  );
}

function formular_buchung_gruppe_gruppe( $notiz_initial = 'Umbuchung' ) {
  open_form( 'small_form', '', '', 'action=buchung_gruppe_gruppe' );
    open_fieldset( '', '', 'Umbuchung von Gruppe an Gruppe' );
      open_table('layout');
        form_row_gruppe( 'von Gruppe:', 'gruppen_id' );
        form_row_gruppe( 'an Gruppe:', 'nach_gruppen_id' );
        form_row_date( 'Valuta:', 'valuta' );
        form_row_betrag( 'Haben Lieferant:' );
        form_row_text( 'Notiz:', 'notiz', 60, $notiz_initial );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function buchung_gruppe_gruppe() {
  global $betrag, $gruppen_id, $nach_gruppen_id, $notiz, $valuta_day, $valuta_month, $valuta_year;
  need_http_var( 'betrag', 'f' );
  need_http_var( 'gruppen_id', 'U' );
  need_http_var( 'nach_gruppen_id', 'U' );
  $notiz or need_http_var( 'notiz', 'H' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  need( sql_gruppe_aktiv( $gruppen_id ) );
  need( sql_gruppe_aktiv( $nach_gruppen_id ) );
  sql_doppelte_transaktion(
    array( 'konto_id' => -1, 'gruppen_id' => $nach_gruppen_id )
  , array( 'konto_id' => -1, 'gruppen_id' => $gruppen_id )
  , $betrag
  , "$year-$month-$day"
  , "$notiz"
  );
}

function formular_buchung_bank_bank( $notiz_initial = 'Überweisung' ) {
  open_form( 'small_form', '', '', 'action=buchung_gruppe_gruppe' );
    open_fieldset( '', '', 'Überweisung von Konto zu Konto' );
      open_table('layout');
        form_row_konto( 'von Konto:', 'konto_id' );
        form_row_kontoauszug( "<div class='right'>Auszug:</div>", 'auszug' );
        form_row_konto( 'an Konto:', 'nach_konto_id' );
        form_row_kontoauszug( "<div class='right'>Auszug:</div>", 'nach_auszug' );
        form_row_date( 'Valuta:', 'valuta' );
        form_row_betrag( 'Haben Lieferant:' );
        form_row_text( 'Notiz:', 'notiz', 60, $notiz_initial );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_buchung_bank_bank() {
  global $betrag, $konto_id, $auszug_jahr, $auszug_nr
       , $nach_konto_id , $nach_auszug_jahr, $nach_auszug_nr
       , $notiz, $valuta_day, $valuta_month, $valuta_year;
  need_http_var( 'betrag', 'f' );
  need_http_var( 'konto_id', 'U' );
  need_http_var( 'auszug_jahr', 'U' );
  need_http_var( 'auszug_nr', 'U' );
  need_http_var( 'nach_konto_id', 'U' );
  need_http_var( 'nach_auszug_jahr', 'U' );
  need_http_var( 'nach_auszug_nr', 'U' );
  need_http_var( 'notiz', 'H' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  sql_doppelte_transaktion(
    array( 'konto_id' => $konto_id, 'auszug_jahr' => $auszug_jahr, 'auszug_nr' => $auszug_nr )
  , array( 'konto_id' => $nach_konto_id, 'auszug_jahr' => $nach_auszug_jahr, 'auszug_nr' => $nach_auszug_nr )
  , $betrag
  , "$valuta_year-$valuta_month-$valuta_day"
  , "$notiz"
  );
}

function formular_buchung_bank_sonderausgabe() {
  open_form( 'small_form', '', '', 'action=buchung_bank_sonderausgabe' );
    open_fieldset( '', '', 'Überweisung Sonderausgabe' );
      open_table('layout');
        form_row_konto( 'von Konto:', 'konto_id' );
        form_row_kontoauszug( "<div class='right'>Auszug:</div>", 'auszug' );
        form_row_date( 'Valuta:', 'valuta' );
        tr_title( 'positiv: Gewinn der FC / negativ: Verlust der FC' );
        form_row_betrag( 'Haben FC:' );
        form_row_text( 'Notiz:', 'notiz', 60 );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_buchung_bank_sonderausgabe() {
  global $betrag, $notiz, $valuta_day, $valuta_month, $valuta_year, $auszug_jahr, $auszug_nr, $konto_id;
  $problems = false;
  // echo "buchung_sonderausgabe: 1";
  need_http_var( 'betrag', 'f' );
  need_http_var( 'notiz', 'H' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  need_http_var( 'konto_id', 'U' );
  need_http_var( 'auszug_jahr', 'U' );
  need_http_var( 'auszug_nr', 'U' );
  if( ! $problems ) {
    sql_doppelte_transaktion(
      array( 'konto_id' => $konto_id, 'auszug_nr' => "$auszug_nr", 'auszug_jahr' => "$auszug_jahr" )
    , array( 'konto_id' => -1, 'gruppen_id' => sql_muell_id(), 'transaktionsart' => TRANSAKTION_TYP_SONDERAUSGABEN )
    , $betrag
    , "$valuta_year-$valuta_month-$valuta_day"
    , "$notiz"
    );
  }
}

function formular_buchung_gruppe_sonderausgabe() {
  open_form( 'small_form', '', '', 'action=buchung_gruppe_sonderausgabe' );
    open_fieldset( '', '', 'Sonderausgabe durch eine Gruppe' );
      open_table('layout');
        form_row_gruppe();
        form_row_date( 'Valuta:', 'valuta' );
        tr_title( 'positiv: Gewinn der FC / negativ: Verlust der FC' );
        form_row_betrag( 'Haben FC:' );
        form_row_text( 'Notiz:', 'notiz', 60 );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_buchung_gruppe_sonderausgabe() {
  global $betrag, $notiz, $valuta_day, $valuta_month, $valuta_year, $gruppen_id, $specialgroups;
  $problems = false;
  // echo "buchung_sonderausgabe: 1";
  $betrag or need_http_var( 'betrag', 'f' );
  $notiz or need_http_var( 'notiz', 'H' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  $gruppen_id or need_http_var( 'gruppen_id', 'U' );
  if( ! $notiz ) {
    div_msg( 'warn', 'Bitte Notiz eingeben!' );
    $problems = true;
  }
  need( sql_gruppe_aktiv( $gruppen_id ) );
  need( sql_gruppenname( $gruppen_id ) );

  if( ! $problems ) {
    sql_doppelte_transaktion(
      array( 'konto_id' => -1, 'gruppen_id' => $gruppen_id )
    , array( 'konto_id' => -1, 'gruppen_id' => sql_muell_id(), 'transaktionsart' => TRANSAKTION_TYP_SONDERAUSGABEN )
    , $betrag
    , "$valuta_year-$valuta_month-$valuta_day"
    , "$notiz"
    );
  }
}

function formular_buchung_gruppe_anfangsguthaben() {
  open_form( 'small_form', '', '', 'action=buchung_gruppe_anfangsguthaben' );
    open_fieldset( '', '', 'Anfangsguthaben einer Gruppe eintragen' );
      open_table('layout');
        open_td( 'kommentar', "colspan='2'" )
          ?>
            Diese Funktion sollte normalerweise
            <em>nur bei Umstellung einer Foodcoop auf die Foodsoft</em> zur Erfassung der
            <em>schon vorhandenen Guthaben schon vorhandener Gruppen</em>
            benutzt werden, <em>nicht</em> im normalen Betrieb!
          <?
        form_row_gruppe();
        form_row_date( 'Valuta:', 'valuta' );
        tr_title( 'positiv: Guthaben der Gruppe / negativ: Schulden der Gruppe' );
        form_row_betrag( 'Haben Gruppe:' );
        form_row_text( 'Notiz:', 'notiz', 60, 'Anfangsguthaben bei Umstellung auf die Foodsoft' );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_buchung_gruppe_anfangsguthaben() {
  global $betrag, $notiz, $valuta_day, $valuta_month, $valuta_year, $gruppen_id, $specialgroups;
  $problems = false;
  // echo "buchung_sonderausgabe: 1";
  $betrag or need_http_var( 'betrag', 'f' );
  $notiz or need_http_var( 'notiz', 'H' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  $gruppen_id or need_http_var( 'gruppen_id', 'U' );
  if( ! $notiz ) {
    div_msg( 'warn', 'Bitte Notiz eingeben!' );
    $problems = true;
  }
  need( sql_gruppe_aktiv( $gruppen_id ) );
  need( sql_gruppenname( $gruppen_id ) );

  if( ! $problems ) {
    sql_doppelte_transaktion(
      array( 'konto_id' => -1, 'gruppen_id' => $gruppen_id )
    , array( 'konto_id' => -1, 'gruppen_id' => sql_muell_id(), 'transaktionsart' => TRANSAKTION_TYP_ANFANGSGUTHABEN )
    , $betrag
    , "$valuta_year-$valuta_month-$valuta_day"
    , "$notiz"
    );
  }
}

function formular_buchung_lieferant_anfangsguthaben() {
  open_form( 'small_form', '', '', 'action=buchung_lieferant_anfangsguthaben' );
    open_fieldset( '', '', 'Anfangsguthaben eines Lieferanten eintragen' );
      open_table('layout');
        open_td( 'kommentar', "colspan='2'" )
          ?>
            Diese Funktion sollte normalerweise
            <em>nur bei Umstellung einer Foodcoop auf die Foodsoft</em> zur Erfassung
            noch offener Rechnungen (Forderungen von Lieferanten an die FC)
            benutzt werden, <em>nicht</em> im laufenden Betrieb!
          <?
        form_row_lieferant();
        form_row_date( 'Valuta:', 'valuta' );
        tr_title( 'positiv: offene Forderung des Lieferanten an die FC / negativ: Forderung der Fc an Lieferant' );
        form_row_betrag( 'Haben Lieferant:' );
        form_row_text( 'Notiz:', 'notiz', 60, 'offene Rechnungen bei Umstellung auf die Foodsoft' );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_buchung_lieferant_anfangsguthaben() {
  global $betrag, $notiz, $valuta_day, $valuta_month, $valuta_year, $lieferanten_id, $specialgroups;
  $problems = false;
  // echo "buchung_sonderausgabe: 1";
  $betrag or need_http_var( 'betrag', 'f' );
  $notiz or need_http_var( 'notiz', 'H' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  need_http_var( 'lieferanten_id', 'U' );
  if( ! $notiz ) {
    div_msg( 'warn', 'Bitte Notiz eingeben!' );
    $problems = true;
  }

  if( ! $problems ) {
    sql_doppelte_transaktion(
      array( 'konto_id' => -1, 'lieferanten_id' => $lieferanten_id )
    , array( 'konto_id' => -1, 'gruppen_id' => sql_muell_id(), 'transaktionsart' => TRANSAKTION_TYP_ANFANGSGUTHABEN )
    , $betrag
    , "$valuta_year-$valuta_month-$valuta_day"
    , "$notiz"
    );
  }
}

function formular_buchung_bank_anfangsguthaben() {
  open_form( 'small_form', '', '', 'action=buchung_bank_anfangsguthaben' );
    open_fieldset( '', '', 'Anfangskontostand eintragen' );
      open_table('layout');
        open_td( 'kommentar', "colspan='2'" )
          ?>
            Diese Funktion sollte normalerweise
            <em>nur bei Umstellung einer Foodcoop auf die Foodsoft</em> zur Erfassung
            <em>des Anfangskontostands bei Umstellung</em>
            benutzt werden, <em>nicht</em> im laufenden Betrieb!
          <?
        form_row_konto( 'von Konto:', 'konto_id' );
        form_row_kontoauszug( "<div class='right'>Auszug:</div>", 'auszug' );
        form_row_date( 'Valuta:', 'valuta' );
        form_row_betrag( 'Kontostand:' );
        form_row_text( 'Notiz:', 'notiz', 60, 'Anfangskontostand bei Umstellung auf die Foodsoft' );
        quad();
        submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}


function action_buchung_bank_anfangsguthaben() {
  global $betrag, $notiz, $valuta_day, $valuta_month, $valuta_year, $auszug_jahr, $auszug_nr, $konto_id;
  $problems = false;
  // echo "buchung_sonderausgabe: 1";
  need_http_var( 'betrag', 'f' );
  need_http_var( 'notiz', 'H' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  need_http_var( 'konto_id', 'U' );
  need_http_var( 'auszug_jahr', 'U' );
  need_http_var( 'auszug_nr', 'U' );
  if( ! $problems ) {
    sql_doppelte_transaktion(
      array( 'konto_id' => $konto_id, 'auszug_nr' => "$auszug_nr", 'auszug_jahr' => "$auszug_jahr" )
    , array( 'konto_id' => -1, 'gruppen_id' => sql_muell_id(), 'transaktionsart' => TRANSAKTION_TYP_ANFANGSGUTHABEN )
    , - $betrag
    , "$valuta_year-$valuta_month-$valuta_day"
    , "$notiz"
    );
  }
}

function formular_umbuchung_verlust( $typ = 0 ) {
  open_form( 'small_form', '', '', "action=umbuchung_verlust,typ=$typ" );
    open_fieldset( '', '', 'Umbuchung Verlustausgleich' );
      open_table('layout');
          open_td( 'label', '', 'von:' );
          open_td( 'kbd' );
            if( $typ ) { 
              need( in_array( $typ, array( TRANSAKTION_TYP_SPENDE, TRANSAKTION_TYP_UMLAGE ) ) );
              echo transaktion_typ_string( $typ );
              hidden_input( 'von_typ', $typ );
            } else {
              open_select( 'von_typ' );
                ?> <option value=''>(bitte Quelle w&auml;hlen)</option> <?
                foreach( array( TRANSAKTION_TYP_SPENDE , TRANSAKTION_TYP_UMLAGE ) as $t ) {
                   ?> <option value='<? echo $t; ?>'><? echo transaktion_typ_string($t); ?></option> <?
                 }
              close_select();
            }
        open_tr();
          open_td( 'label', '', 'nach:' );
          open_td( 'kbd' );
            open_select( 'nach_typ' );
              ?> <option value=''>(bitte Ziel w&auml;hlen)</option> <?
              foreach( array( TRANSAKTION_TYP_AUSGLEICH_ANFANGSGUTHABEN
                            , TRANSAKTION_TYP_AUSGLEICH_SONDERAUSGABEN
                            , TRANSAKTION_TYP_AUSGLEICH_BESTELLVERLUSTE ) as $t ) {
                ?> <option value='<? echo $t; ?>'><? echo transaktion_typ_string($t); ?></option> <?
              }
            close_select();
        form_row_date( 'Valuta:', 'valuta' );
        form_row_betrag( 'Betrag:' );
        form_row_text( 'Notiz:', 'notiz', 60 );
          quad();
          submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_umbuchung_verlust() {
  global $von_typ, $nach_typ, $valuta_day, $valuta_month, $valuta_year, $betrag, $notiz;

  need_http_var( 'von_typ', 'U' );
  need_http_var( 'nach_typ', 'U' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  need_http_var( 'betrag', 'f' );
  need_http_var( 'notiz', 'H' );
  need( in_array( $von_typ, array( TRANSAKTION_TYP_SPENDE, TRANSAKTION_TYP_UMLAGE ) ) );
  need( in_array( $nach_typ, array( TRANSAKTION_TYP_AUSGLEICH_ANFANGSGUTHABEN
                                  , TRANSAKTION_TYP_AUSGLEICH_SONDERAUSGABEN
                                  , TRANSAKTION_TYP_AUSGLEICH_BESTELLVERLUSTE ) ) );
  switch( $von_typ ) {
    case TRANSAKTION_TYP_SPENDE:
      $von_typ = TRANSAKTION_TYP_UMBUCHUNG_SPENDE;
      break;
    case TRANSAKTION_TYP_UMLAGE:
      $von_typ = TRANSAKTION_TYP_UMBUCHUNG_UMLAGE;
  }
  if( ! $notiz ) {
    div_msg( 'warn', 'Bitte Notiz eingeben!' );
    break;
  }
  sql_doppelte_transaktion(
    array( 'konto_id' => -1, 'gruppen_id' => sql_muell_id(), 'transaktionsart' => $nach_typ )
  , array( 'konto_id' => -1, 'gruppen_id' => sql_muell_id(), 'transaktionsart' => $von_typ )
  , $betrag
  , "$valuta_year-$valuta_month-$valuta_day"
  , "$notiz"
  );
}

function formular_gruppen_umlage() {
  open_form( 'small_form', '', '', 'action=buchung_umlage' );
    open_fieldset( '', '', 'Verlustumlage auf Gruppenmitglieder' );
      open_table( 'layout' );
          open_td( '', "colspan='2'", "Von <span class='bold italic'>allen aktiven Bestellgruppen</span> eine Umlage" );
        form_row_betrag( 'in Höhe von' );
          echo " EUR je Gruppenmitglied erheben";
        form_row_date( 'Valuta:', 'valuta' );
        form_row_text( 'Notiz:', 'notiz', 60 );
          quad();
          submission_button( 'OK' );
      close_table();
    close_fieldset();
  close_form();
}

function action_gruppen_umlage() {
  global $valuta_day, $valuta_month, $valuta_year, $betrag, $notiz;

  $problems = false;
  need_http_var( 'betrag', 'f' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  need_http_var( 'notiz', 'H' );
  if( ! $notiz ) {
    div_msg( 'warn', 'Bitte Notiz eingeben!' );
    $problems = true;
  }
  if( ! $problems ) {
    foreach( sql_aktive_bestellgruppen() as $gruppe ) {
      if( $gruppe['mitgliederzahl'] > 0 ) {
        sql_doppelte_transaktion(
          array( 'konto_id' => -1, 'gruppen_id' => sql_muell_id(), 'transaktionsart' => TRANSAKTION_TYP_UMLAGE )
        , array( 'konto_id' => -1, 'gruppen_id' => $gruppe['id'], 'transaktionsart' => TRANSAKTION_TYP_UMLAGE )
        , $betrag * $gruppe['mitgliederzahl']
        , "$valuta_year-$valuta_month-$valuta_day"
        , "$notiz"
        );
      }
    }
  }
}

function mod_onclick( $id ) {
  return $id ? " onclick=\"document.getElementById('$id').className='modified';\" " : '';
}

function formular_artikelnummer( $produkt_id, $toggle = false, $mod_id = false ) {
  $produkt = sql_produkt_details( $produkt_id );
  $anummer = $produkt['artikelnummer'];
  $lieferanten_id = $produkt['lieferanten_id'];

  open_fieldset( 'small_form', '', "Artikelnummer ($anummer) &auml;ndern", $toggle );
    open_table( 'layout' );
        open_td( '', '', 'neue Artikel-Nr. setzen:' );
          open_form( 'small_form', '', '', 'action=artikelnummer_setzen' );
            echo string_view( $anummer, 20, 'anummer' );
            submission_button();
          close_form();
      open_tr();
        open_td( '', '', '...oder: Katalogsuche nach:' );
          open_form('small_form','','', "produkt_id=$produkt_id,lieferanten_id=$lieferanten_id");
            echo string_view( $produkt['name'], 40, 'name' );
            echo fc_link( 'artikelsuche', 'text=Los!,form,class=button' );
          close_form();
    close_table();
  close_fieldset();
}


function formular_produktpreis( $produkt, $preiseintrag, $prgueltig ) {

  if( ! $preiseintrag['gebindegroesse'] ) {
    if( $prgueltig and $produkt['gebindegroesse'] > 1 )
      $preiseintrag['gebindegroesse'] = $produkt['gebindegroesse'];
    else
      $preiseintrag['gebindegroesse'] = 1;
  }

  if( ! $preiseintrag['verteileinheit'] ) {
    if( $prgueltig )
      $preiseintrag['verteileinheit'] =
        ( ( $produkt['kan_verteilmult'] > 0.0001 ) ? $produkt['kan_verteilmult'] : 1 )
        . ( $produkt['kan_verteileinheit'] ? " {$produkt['kan_verteileinheit']} " : ' ST' );
    else
      $preiseintrag['verteileinheit'] = '1 ST';
  }

  if( ! $preiseintrag['liefereinheit'] ) {
    if( $prgueltig and $produkt['kan_liefereinheit'] and ( $produkt['kan_liefermult'] > 0.0001 ) )
      $preiseintrag['liefereinheit'] = "{$produkt['kan_liefermult']} {$produkt['kan_liefereinheit']}";
    else
      $preiseintrag['liefereinheit'] = $preiseintrag['verteileinheit'];
  }

  if( ! $preiseintrag['mwst'] ) {
    if( $prgueltig and $produkt['mwst'] )
      $preiseintrag['mwst'] = $produkt['mwst'];
    else
      $preiseintrag['mwst'] = '7.00';
  }

  if( ! $preiseintrag['pfand'] ) {
    if( $prgueltig and $produkt['pfand'] )
      $preiseintrag['pfand'] = $produkt['pfand'];
    else
      $preiseintrag['pfand'] = '0.00';
  }

  if( ! $preiseintrag['preis'] ) {
    if( $prgueltig and $produkt['endpreis'] )
      $preiseintrag['preis'] = $produkt['endpreis'];
    else
      $preiseintrag['preis'] = '0.00';
  }

  if( ! $preiseintrag['bestellnummer'] ) {
    if( $prgueltig and $produkt['bestellnummer'] )
      $preiseintrag['bestellnummer'] = $produkt['bestellnummer'];
    else
      $preiseintrag['bestellnummer'] = '';
  }

  if( ! $preiseintrag['notiz'] ) {
    if( $prgueltig and $produkt['notiz'] )
      $preiseintrag['notiz'] = $produkt['notiz'];
    else
      $preiseintrag['notiz'] = '';
  }

  // echo "newverteileinheit: {$preiseintrag['verteileinheit']}";
  // echo "newliefereinheit: {$preiseintrag['liefereinheit']}";

  // restliche felder automatisch berechnen:
  //
  preisdatenSetzen( & $preiseintrag );

  $form_id = open_form( 'small_form', '', '', 'action=neuer_preiseintrag' );

    open_table();
    // <table id='preisform'>
      form_row_text( 'Produkt:', false, 1, "{$produkt['name']} von {$produkt['lieferanten_name']}" );

      tr_title( 'Notiz: zum Beispiel aktuelle Herkunft, Verband oder Lieferant' );
      form_row_text( 'Notiz:', 'notiz;', 42, $preiseintrag['notiz'] );

      form_row_text( 'Bestell-Nr:', 'bestellnummer', 8, $preiseintrag['bestellnummer'] );
        ?>
          <label>MWSt:</label>
          <input type='text' size='4' name='mwst' id='newfcmwst'
           value='<? echo $preiseintrag['mwst']; ?>'
           title='MWSt-Satz in Prozent'
           onchange='preisberechnung_rueckwaerts();'>
  
          <label>Pfand:</label>
          <input type='text' size='4' name='pfand' id='newfcpfand'
           value='<? echo $preiseintrag['pfand']; ?>'
           title='Pfand pro V-Einheit, bei uns immer 0.00 oder 0.16'
           onchange='preisberechnung_rueckwaerts();'>
        </td>
      </tr>
        <td><label>Verteil-Einheit:</label></td>
        <td>
          <input type='text' size='4' name='verteilmult' id='newfcmult'
           value='<? echo $preiseintrag['kan_verteilmult']; ?>'
           title='Vielfache der Einheit: meist 1, ausser bei g, z.B. 1000 fuer 1kg'
           onchange='preisberechnung_fcmult();'>
          <select size='1' name='verteileinheit' id='newfceinheit'
            onchange='preisberechnung_default();'>
            <? echo optionen_einheiten( $preiseintrag['kan_verteileinheit'] ); ?>
          </select>
          <label>Endpreis:</label>
          <input title='Preis incl. MWSt und Pfand' type='text' size='8' id='newfcpreis' name='preis'
           value='<? echo $preiseintrag['preis']; ?>'
           onchange='preisberechnung_vorwaerts();'>
          / <span id='newfcendpreiseinheit'>
              <? echo $preiseintrag['kan_verteilmult']; ?>
              <? echo $preiseintrag['kan_verteileinheit']; ?>
             </span>
  
          <label>Gebinde:</label>
          <input type='text' size='4' name='gebindegroesse' id='newfcgebindegroesse'
           value='<? echo $preiseintrag['gebindegroesse']; ?>'
           title='Gebindegroesse in ganzen Vielfachen der V-Einheit'
           onchange='preisberechnung_gebinde();'>
          * <span id='newfcgebindeeinheit']>
              <? echo $preiseintrag['kan_verteilmult']; ?>
              <? echo $preiseintrag['kan_verteileinheit']; ?>
            </span>
        </td>
      </tr>
      <tr>
        <td><label>Liefer-Einheit:</label></td>
        <td>
          <input type='text' size='4' name='liefermult' id='newliefermult'
           value='<? echo $preiseintrag['kan_liefermult']; ?>'
           title='Vielfache der Einheit: meist 1, ausser bei g, z.B. 1000 fuer 1kg'
           onchange='preisberechnung_default();'>
          <select size='1' name='liefereinheit' id='newliefereinheit'
            onchange='preisberechnung_default();'>
            <? echo optionen_einheiten( $preiseintrag['kan_liefereinheit'] ); ?>
          </select>
  
          <label>Lieferpreis:</label>
            <input title='Nettopreis' type='text' size='8' id='newfclieferpreis' name='lieferpreis'
             value='<? echo $preiseintrag['lieferpreis']; ?>'
             onchange='preisberechnung_rueckwaerts();'>
            / <span id='newfcpreiseinheit'><? echo $preiseintrag['preiseinheit']; ?></span>
        </td>
      </tr>
      <tr>
        <td><label>ab:</label></td>
        <td>
          <? date_selector( 'day', date('d'), 'month', date('m'), 'year', date('Y') ); ?>
          <label>&nbsp;</label>
          <input type='submit' name='submit' value='OK'
           onclick=\"document.getElementById('row$outerrow').className='modified';\";
           title='Neuen Preiseintrag vornehmen (und letzten ggf. abschliessen)'>
  
          <label>&nbsp;</label>
          <label>Dynamische Neuberechnung:</label>
          <input name='dynamischberechnen' type='checkbox' value='yes'
           title='Dynamische Berechnung anderer Felder bei Änderung eines Eintrags' checked>
  
        </td>
      </tr>
    </table>
 <? 
   close_form();
  close_fieldset();
  
  ?>
  <script type="text/javascript">
  
    var mwst, pfand, verteilmult, verteileinheit, preis, gebindegroesse,
      liefermult, liefereinheit, lieferpreis, preiseinheit, mengenfaktor;
  
    var preisform = '<? echo "form_$form_id"; ?>';

    // vorwaerts: lieferpreis berechnen
    //
    var vorwaerts = 0;
  
    function preiseinheit_setzen() {
      if( liefereinheit != verteileinheit ) {
        mengenfaktor = gebindegroesse;
        preiseinheit = liefereinheit + ' (' + gebindegroesse * verteilmult + ' ' + verteileinheit + ')';
        if( liefermult != '1' )
          preiseinheit = liefermult + ' ' + preiseinheit;
      } else {
        switch( liefereinheit ) {
          case 'g':
            preiseinheit = 'kg';
            mengenfaktor = 1000 / verteilmult;
            break;
          case 'ml':
            preiseinheit = 'L';
            mengenfaktor = 1000 / verteilmult;
            break;
          default:
            preiseinheit = liefereinheit;
            mengenfaktor = 1.0 / verteilmult;
            break;
        }
      }
    }
  
    function preiseintrag_auslesen() {
      mwst = parseFloat( document.forms['preisform'].newfcmwst.value );
      pfand = parseFloat( document.forms['preisform'].newfcpfand.value );
      verteilmult = parseInt( document.forms['preisform'].newfcmult.value );
      verteileinheit = document.forms['preisform'].newfceinheit.value;
      preis = parseFloat( document.forms['preisform'].newfcpreis.value );
      gebindegroesse = parseInt( document.forms['preisform'].newfcgebindegroesse.value );
      liefermult = parseInt( document.forms['preisform'].newliefermult.value );
      liefereinheit = document.forms['preisform'].newliefereinheit.value;
      lieferpreis = parseFloat( document.forms['preisform'].newfclieferpreis.value );
      preiseinheit_setzen();
    }
  
    preiseintrag_auslesen();
  
    function preiseintrag_update() {
      document.forms['preisform'].newfcmwst.value = mwst;
      document.forms['preisform'].newfcmwst.pfand = pfand;
      document.forms['preisform'].newfcmult.value = verteilmult;
      document.forms['preisform'].newfceinheit.value = verteileinheit;
      document.forms['preisform'].newfcpreis.value = preis;
      document.forms['preisform'].newfcgebindegroesse.value = gebindegroesse;
      document.forms['preisform'].newliefermult.value = liefermult;
      document.forms['preisform'].newliefereinheit.value = liefereinheit;
      document.forms['preisform'].newfclieferpreis.value = lieferpreis;
      document.getElementById("newfcendpreiseinheit").firstChild.nodeValue = verteilmult + ' ' + verteileinheit;
      document.getElementById("newfcgebindeeinheit").firstChild.nodeValue = verteilmult + ' ' + verteileinheit;
      document.getElementById("newfcpreiseinheit").firstChild.nodeValue = preiseinheit;
    }
  
    function preisberechnung_vorwaerts() {
      vorwaerts = 1;
      preiseintrag_auslesen();
      berechnen = document.forms['preisform'].dynamischberechnen.checked;
      if( berechnen ) {
        lieferpreis = 
          parseInt( 0.499 + 100 * ( preis - pfand ) / ( 1.0 + mwst / 100.0 ) * mengenfaktor ) / 100.0;
      }
      preiseintrag_update();
    }
  
    function preisberechnung_rueckwaerts() {
      vorwaerts = 0;
      preiseintrag_auslesen();
      berechnen = document.forms['preisform'].dynamischberechnen.checked;
      if( berechnen ) {
        preis = 
          parseInt( 0.499 + 10000 * ( lieferpreis * ( 1.0 + mwst / 100.0 ) / mengenfaktor + pfand ) ) / 10000.0;
      }
      preiseintrag_update();
    }
  
    function preisberechnung_default() {
      if( vorwaerts )
        preisberechnung_vorwaerts();
      else
        preisberechnung_rueckwaerts();
    }
    function preisberechnung_fcmult() {
      alt = verteilmult;
      berechnen = document.forms['preisform'].dynamischberechnen.checked;
      if( berechnen ) {
        verteilmult = parseInt( document.forms['preisform'].newfcmult.value );
        if( verteilmult < 1 )
          verteilmult = 1;
        if( (verteilmult > 0) && (alt > 0) ) {
          gebindegroesse = parseInt( 0.499  + gebindegroesse * alt / verteilmult);
          if( gebindegroesse < 1 )
            gebindegroesse = 1;
          document.forms['preisform'].newfcgebindegroesse.value = gebindegroesse;
        }
      }
      preisberechnung_default();
    }
    function preisberechnung_gebinde() {
      alt = gebindegroesse;
      berechnen = document.forms['preisform'].dynamischberechnen.checked;
      if( berechnen ) {
        gebindegroesse = parseInt( document.forms['preisform'].newfcgebindegroesse.value );
        if( gebindegroesse < 1 )
          gebindegroesse = 1;
        // if( (gebindegroesse > 0) && (alt > 0) ) {
        //  verteilmult = parseInt( 0.499 + verteilmult * alt / gebindegroesse );
        //  document.forms['preisform'].newfcmult.value = verteilmult;
        // }
      }
      preisberechnung_default();
    }
  
  </script>
  <?
}


// fieldset_edit_transaction: ediert eine transaktion der beiden transaktionen einer buchung.
// $tag: 1 oder 2:
//  - felder die in beiden transactions identisch sein muessen, werden nur bei $tag == 1 angezeigt
//  - $tag wird an feldnamen angehaengt, um beide transaktionen unterscheiden zu koennen.
//
function fieldset_edit_transaction( $id, $tag, $editable ) {
  global $selectable_types;

  $muell_id = sql_muell_id();
  $t = sql_get_transaction( $id );

  $haben = $t['haben'];
  $soll = -$haben;

  hidden_input( "id_$tag", $id );

  if( $tag == 1 ) {
    open_tr();
      open_td('label', '', 'Buchung:' );
      open_td('kbd' );
        open_div('kbd', '', $t['buchungsdatum'] );
        open_div('kbd small', '', $t['dienst_name'] );
    form_row_date( 'Valuta:', $editable ? 'valuta' : false, $t['valuta'] );
    form_row_text( 'Notiz:', $editable ? 'notiz' : false, 42, $t['kommentar'] );
  }

  if( $id > 0 ) {  // bank-transaktion
    open_tr();
      open_th( 'smallskip', "colspan='2'", "Bank-Transaktion <span class='small'>$id</span>" );
    form_row_konto( 'Konto:', false, $t['konto_id'] );   // TODO: make this editable?
    form_row_kontoauszug( 'Kontoauszug:', $editable ? "auszug_$tag" : false, $t['kontoauszug_jahr'], $t['kontoauszug_nr'] ); 
    tr_title( 'Haben FC: positiv, falls zu unseren Gunsten (wie auf Kontoauszug der Bank)' );
    form_row_betrag( 'Haben FC:', ( $editable and $tag == 1 ) ? 'haben' : false, $haben );

  } else {  // lieferant / gruppe / muell-transaktion
    $id = -$id;
    $gruppen_id = $t['gruppen_id'];
    $lieferanten_id = $t['lieferanten_id'];

    if( $lieferanten_id > 0 ) {
      open_tr();
        open_th( 'smallskip', "colspan='2'", "Lieferanten-Transaktion <span class='small'>$id</span>" );
      form_row_lieferant( 'Lieferant:', false, $t['lieferanten_id'] );  // TODO: make this editable?
      if( $haben > 0 ) {
        tr_title( 'Haben FC: positiv, falls wir unsere Schulden beim Lieferanten verringern' );
        form_row_betrag( 'Haben FC:', ( $editable and $tag == 1 ) ? 'haben' : false, $haben );
      } else {
        tr_title( 'Soll FC: positiv, falls wir unsere Schulden beim Lieferanten vergroessern' );
        form_row_betrag( 'Soll FC:', ( $editable and $tag == 1 ) ? 'soll' : false, $soll );
      }

    } else if( $gruppen_id == $muell_id ) {
      open_tr();
        open_th( 'smallskip', "colspan='2'", "Interne Verrechnung <span class='small'>$id</span>" );
      open_tr();
        open_td( 'label', '', 'Typ:' );
        open_td( 'kbd' );
          $typ = $t['transaktionstyp'];
          $options = '';
          $selected = false;
          foreach( $selectable_types as $tt ) {
            $options .= "<option value='".$tt."'";
            if( $tt == $typ ) {
              $options .= " selected";
              $selected = true;
            }
            $options .= ">" . transaktion_typ_string($tt) . "</option>";
          }
          if( ! $selected ) {
            $options = "<option value=''>(bitte Typ wählen)</option>$options";
          }
          if( $editable and ( $selected or ( $typ == TRANSAKTION_TYP_UNDEFINIERT ) ) ) {
            open_select( "typ_$tag" );
              echo $options;
            close_select();
          } else {
            echo transaktion_typ_string( $typ );
          }

          if( $haben > 0 ) { // <-- SIC: "bookkeeper view" vs. "shareholder view"!
            tr_title( 'Soll FC: positiv, falls wir Verlust gemacht haben' );
            form_row_betrag( 'Soll FC:', ( $editable and $tag == 1 ) ? 'soll' : false, $soll );
          } else {
            tr_title( 'Haben FC: positiv, falls wir Gewinn gemacht haben' );
            form_row_betrag( 'Haben FC:', ( $editable and $tag == 1 ) ? 'haben' : false, $haben );
          }

    } else {  // regulaere (nicht-13) gruppen-transaktion
      open_tr();
        open_th( 'smallskip', "colspan='2'", "Gruppen-Transaktion <span class='small'>$id</span>" );
      form_row_gruppe( 'Gruppe:', false, $t['gruppen_id'] );  // TODO: make this editable?
      tr_title( 'Haben Gruppe: positiv, wenn die Gruppe jetzt mehr Geld auf dem Gruppenkonto hat' );
      form_row_betrag( 'Haben Gruppe:', ( $editable and $tag == 1 ) ? 'soll' : false, $soll );
    }
  }
}

?>