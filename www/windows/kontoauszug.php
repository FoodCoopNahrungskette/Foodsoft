<?PHP

assert($angemeldet) or exit();
 
setWindowSubtitle( 'Kontoauszug' );
setWikiHelpTopic( 'foodsoft:kontoauszug' );

need_http_var( 'konto_id', 'u', true );
need_http_var( 'auszug_jahr', 'u', true );
need_http_var( 'auszug_nr', 'u', true );

$auszug = sql_kontoauszug( $konto_id, $auszug_jahr, $auszug_nr );
// need( mysql_num_rows( $auszug ) > 0, "Keine Posten vorhanden" );

$result = sql_saldo( $konto_id, $auszug_jahr, $auszug_nr-1 );
if( mysql_num_rows( $result ) < 1 ) {
  $startsaldo = 0.0;
} else {
  need( mysql_num_rows( $result ) == 1 );
  $row = mysql_fetch_array( $result );
  $startsaldo = $row['saldo'];
}

$result = sql_saldo( $konto_id, $auszug_jahr, $auszug_nr );
need( mysql_num_rows( $result ) == 1 );
$row = mysql_fetch_array( $result );
$saldo = $row['saldo'];
$bankname = $row['name'];

get_http_var( 'action', 'w', false );

if( $action == 'zahlung_gruppe' ) {
  need_http_var( 'betrag', 'f' );
  need_http_var( 'gruppen_id', 'u' );
  $gruppen_name = sql_gruppenname( $gruppen_id );
  if( $betrag < 0 ) {
    need_http_var( 'notiz', 'M' );
  } else {
    get_http_var( 'notiz', 'M', "Einzahlung Gruppe $gruppen_name" );
  }
  need_http_var( 'day', 'u' );
  need_http_var( 'month', 'u' );
  need_http_var( 'year', 'u' );
  $von = false;  // FIXME: einfache buchungen sollte es nicht geben...
  $nach = array(
    'konto_id' => $konto_id
  , 'auszug_jahr' => $auszug_jahr
  , 'auszug_nr' => $auszug_nr
  , 'gruppen_id' => $gruppen_id
  );
  sql_konto_transaktion( $von, $nach, $betrag, "$year-$month-$day", $notiz );
}

if( $action == 'zahlung_lieferant' ) {
  need_http_var( 'betrag', 'f' );
  need_http_var( 'lieferant_id', 'u' );
  need_http_var( 'day', 'u' );
  need_http_var( 'month', 'u' );
  need_http_var( 'year', 'u' );
  need_http_var( 'notiz', 'M' );
  $von = array(
    'konto_id' => $konto_id
  , 'auszug_jahr' => $auszug_jahr
  , 'auszug_nr' => $auszug_nr
  , 'lieferant_id' => $lieferant_id
  );
  $nach = false;  // FIXME: einfache buchungen sollte es nicht geben...
  sql_konto_transaktion( $von, $nach, $betrag, "$year-$month-$day", $notiz );
}

if( $action == 'zahlung_gruppelieferant' ) {
  need_http_var( 'betrag', 'f' );
  need_http_var( 'lieferant_id', 'u' );
  need_http_var( 'gruppen_id', 'u' );
  need_http_var( 'day', 'u' );
  need_http_var( 'month', 'u' );
  need_http_var( 'year', 'u' );
  need_http_var( 'notiz', 'M' );
  $von = array(
    'konto_id' => $konto_id
  , 'auszug_jahr' => $auszug_jahr
  , 'auszug_nr' => $auszug_nr
  , 'gruppen_id' => $gruppen_id
  );
  $nach = array(
    'konto_id' => $konto_id
  , 'auszug_jahr' => $auszug_jahr
  , 'auszug_nr' => $auszug_nr
  , 'lieferant_id' => $lieferant_id
  );
  sql_konto_transaktion( $von, $nach, $betrag, "$year-$month-$day", $notiz );
}

if( $action == 'zahlung_gruppegruppe' ) {
  need_http_var( 'betrag', 'f' );
  need_http_var( 'lieferant_id', 'u' );
  need_http_var( 'gruppen_id', 'u' );
  need_http_var( 'day', 'u' );
  need_http_var( 'month', 'u' );
  need_http_var( 'year', 'u' );
  need_http_var( 'notiz', 'M' );
  $von = array(
    'konto_id' => $konto_id
  , 'auszug_jahr' => $auszug_jahr
  , 'auszug_nr' => $auszug_nr
  , 'gruppen_id' => $von_gruppen_id
  );
  $nach = array(
    'konto_id' => $konto_id
  , 'auszug_jahr' => $auszug_jahr
  , 'auszug_nr' => $auszug_nr
  , 'gruppen_id' => $nach_gruppen_id
  );
  sql_konto_transaktion( $von, $nach, $betrag, "$year-$month-$day", $notiz );
}

echo "<h1>Kontoauszug: $bankname - $auszug_jahr / $auszug_nr</h1>";

?>
  <div id='transactions_button' style='padding-bottom:1em;'>
  <span class='button'
    onclick="document.getElementById('transactions_menu').style.display='block';
             document.getElementById('transactions_button').style.display='none';"
    >Transaktion eintragen...</span>
  </div>

  <fieldset class='small_form' id='transactions_menu' style='display:none;margin-bottom:2em;'>
    <legend>
      <img src='img/close_black_trans.gif' class='button' title='Schliessen' alt='Schliessen'
      onclick="document.getElementById('transactions_button').style.display='block';
               document.getElementById('transactions_menu').style.display='none';">
      Transaktionen
    </legend>

    Art der Transaktion:

    <ul style='list-style:none;'>
      <li title='Einzahlung von oder Auszahlung an Gruppe'>
      <input type='radio' name='transaktionsart'
        onclick="document.getElementById('gruppe_form').style.display='block';
                 document.getElementById('lieferant_form').style.display='none';
                 document.getElementById('gruppelieferant_form').style.display='none';
                 document.getElementById('gruppegruppe_form').style.display='none';"
      ><b>Einzahlung / Auszahlung Gruppe</b>
      </li>

      <li title='Überweisung an oder Lastschrift Lieferant'>
      <input type='radio' name='transaktionsart'
        onclick="document.getElementById('gruppe_form').style.display='none';
                 document.getElementById('lieferant_form').style.display='block';
                 document.getElementById('gruppelieferant_form').style.display='none';
                 document.getElementById('gruppegruppe_form').style.display='none';"
      ><b>Überweisung / Abbuchung Lieferant</b>
      </li>

      <li title='Direkte Überweisung einer Gruppe an einen Lieferanten'>
      <input type='radio' name='transaktionsart'
        onclick="document.getElementById('gruppe_form').style.display='none';
                 document.getElementById('lieferant_form').style.display='none';
                 document.getElementById('gruppelieferant_form').style.display='block';
                 document.getElementById('gruppegruppe_form').style.display='none';"
      ><b>Zahlung Gruppe -> Lieferant</b>
      </li>

      <li title='überweisung von einer Gruppe an eine andere Gruppe'>
      <input type='radio' name='transaktionsart'
        onclick="document.getElementById('gruppe_form').style.display='none';
                 document.getElementById('lieferant_form').style.display='none';
                 document.getElementById('gruppelieferant_form').style.display='none';
                 document.getElementById('gruppegruppe_form').style.display='block';"
      ><b>Umbuchung Gruppe -> Gruppe</b>
      </li>
    </ul>

    <div id='gruppe_form' style='display:none;'>
      <form method='post' class='small_form' action='<? echo self_url(); ?>'>
        <? echo self_post(); ?>
        <input type='hidden' name='action' value='zahlung_gruppe'>
        <fieldset>
          <legend>
            Einzahlung / Auszahlung Gruppe
          </legend>
          <table>
            <tr>
              <td><label>Gruppe:</label></td>
              <td><select name='gruppen_id'><? echo optionen_gruppen(); ?></select></td>
            </tr>
            <tr>
              <td><label>Datum:</label></td>
              <td><? date_selector( 'day', date('d'), 'month', date('m'), 'year', date('Y') ); ?></td>
            </tr>
            <tr>
              <td><label title'positiv: Einzahlung / negativ: Auszahlung!'>Betrag:</label></td>
              <td>
                <input type="text" name="betrag" value="">
                <input style='margin-left:2em;' type='submit' name='Ok' value='Ok'>
              </td>
            </tr>
            <tr>
              <td>Notiz:</td>
              <td><input type="text" size="60" name="notiz"></td>
            </tr>
          </table>
        </fieldset>
      </form>
    </div>

    <div id='lieferant_form' style='display:none;'>
      <form method='post' class='small_form' action='<? echo self_url(); ?>'>
        <? echo self_post(); ?>
        <input type='hidden' name='action' value='zahlung_lieferant'>
        <fieldset>
          <legend>
            Überweisung / Lastschrift Lieferant
          </legend>
          <table>
            <tr>
              <td><label>Lieferant:</label></td>
              <td><select name='lieferant_id'><? echo optionen_lieferanten(); ?></select></td>
            </tr>
            <tr>
              <td><label>Datum:</label></td>
              <td><? date_selector( 'day', date('d'), 'month', date('m'), 'year', date('Y') ); ?></td>
            </tr>
            <tr>
              <td><label title'positiv: Einzahlung / negativ: Auszahlung!'>Betrag:</label></td>
              <td>
                <input type="text" name="betrag" value="">
                <input style='margin-left:2em;' type='submit' name='Ok' value='Ok'>
              </td>
            </tr>
            <tr>
              <td>Notiz:</td>
              <td><input type="text" size="60" name="notiz"></td>
            </tr>
          </table>
        </fieldset>
      </form>
    </div>

    <div id='gruppelieferant_form' style='display:none;'>
      <form method='post' class='small_form' action='<? echo self_url(); ?>'>
        <? echo self_post(); ?>
        <input type='hidden' name='action' value='zahlung_gruppelieferant'>
        <fieldset>
          <legend>
            Zahlung von Gruppe an Lieferant
          </legend>
          <table>
            <tr>
              <td><label>von Gruppe:</label></td>
              <td><select name='gruppen_id'><? echo optionen_gruppen(); ?></select></td>
            </tr>
            <tr>
              <td><label>an Lieferant:</label></td>
              <td><select name='lieferant_id'><? echo optionen_lieferanten(); ?></select></td>
            </tr>
            <tr>
              <td><label>Datum:</label></td>
              <td><? date_selector( 'day', date('d'), 'month', date('m'), 'year', date('Y') ); ?></td>
            </tr>
            <tr>
              <td><label>Betrag:</label></td>
              <td>
                <input type="text" name="betrag" value="">
                <input style='margin-left:2em;' type='submit' name='Ok' value='Ok'>
              </td>
            </tr>
            <tr>
              <td>Notiz:</td>
              <td><input type="text" size="60" name="notiz"></td>
            </tr>
          </table>
        </fieldset>
      </form>
    </div>

    <div id='gruppegruppe_form' style='display:none;'>
      <form method='post' class='small_form' action='<? echo self_url(); ?>'>
        <? echo self_post(); ?>
        <input type='hidden' name='action' value='umbuchung_gruppegruppe'>
        <fieldset>
          <legend>
            Umbuchung von Gruppe an Gruppe
          </legend>
          <table>
            <tr>
              <td><label>von Gruppe:</label></td>
              <td><select name='von_gruppen_id'><? echo optionen_gruppen(); ?></select></td>
            </tr>
            <tr>
              <td><label>an Gruppe:</label></td>
              <td><select name='nach_gruppen_id'><? echo optionen_gruppen(); ?></select></td>
            </tr>
            <tr>
              <td><label>Datum:</label></td>
              <td><? date_selector( 'day', date('d'), 'month', date('m'), 'year', date('Y') ); ?></td>
            </tr>
            <tr>
              <td><label>Betrag:</label></td>
              <td>
                <input type="text" name="betrag" value="">
                <input style='margin-left:2em;' type='submit' name='Ok' value='Ok'>
              </td>
            </tr>
            <tr>
              <td>Notiz:</td>
              <td><input type="text" size="60" name="notiz"></td>
            </tr>
          </table>
        </fieldset>
      </form>
    </div>

  </fieldset>

<?









?>
  <table class='liste'>
    <tr class='legende'>
      <th>Nr</th>
      <th>Text</th>
      <th>Betrag</th>
    </tr>
<?

printf( "
    <tr class='summe'>
      <td colspan='2' style='text-align:right;'>Startsaldo:</td>
      <td class='number'>%.2lf</td>
    </tr>
  "
, $startsaldo
);

$n=0;
while( $row = mysql_fetch_array( $auszug ) ) {
  $n++;
  echo "
    <tr>
      <td class='number'>$n</td>
      <td>
  ";
  $gid = $row['gruppen_id'];
  $lid = $row['lieferanten_id'];
  $kommentar = $row['kommentar'];
  if( $gid ) {
    printf( "<p>Überweisung Gruppe %d (%s)<p>" , $gid % 1000, sql_gruppenname( $gid ) );
  }
  if( $lid ) {
    printf( "<p>Überweisung Lieferant %s<p>" , lieferant_name( $lid ) );
  }
  if( $kommentar ) {
    echo "<p>$kommentar</p>";
  }
  printf( "<td class='number' style='vertical-align:bottom;'>%.2lf</td>", $row['betrag'] );
  echo "</tr>";
}

printf( "
    <tr class='summe'>
      <td colspan='2' style='text-align:right;'>Saldo:</td>
      <td class='number'>%.2lf</td>
    </tr>
  "
, $saldo
);

?> </table> <?


?>

