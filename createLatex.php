<?php

$cfgName = 'inputData.cfg';
$persDataFilename = 'persData.cfg';

if (!file_exists($cfgName)) {
    $emptyInputData = [
        'Anrede' => '',
        'Vorname' => '',
        'Nachname' => '',
        'AddresseTeil1' => '',
        'AddresseTeil2' => '',
        'Positionen' => [
            ''
        ],
        'Rechnungsnummer' => ''
    ];
    file_put_contents($cfgName, json_encode($emptyInputData));
    echo 'created empty config file. fill with data to create a complete invoice.';
    return;
}

if (!file_exists($persDataFilename)) {
    $persDataFilenameData = [
        'Vorname' => '',
        'Nachname' => '',
        'AddresseTeil1' => '',
        'AddresseTeil2' => '',
        'Telefon' => '',
        'eMail' => '',
        'Steuernummer' => '',
        'IBAN' => '',
        'BIC' => '',
        'Bankname' => ''
    ];
    file_put_contents($persDataFilename, json_encode($persDataFilenameData));
    echo 'created empty config file. fill with data to create a complete invoice.';
    return;
}

$persData = file_get_contents($persDataFilename);
$persDataArr = json_decode($persData, true);

$cfg = file_get_contents($cfgName);
$cfgArr = json_decode($cfg, true);

$now = new \DateTime('now');
$nowString = $now->format('d-m-Y');
$filename = 'Rechnung-' . $nowString . '.tex';
touch($filename);

//input data
$anrede = $cfgArr['Anrede'];
$destinationFirstName = $cfgArr['Vorname'];
$destinationLastName = $cfgArr['Nachname'];
$destinationAddress1 = $cfgArr['AddresseTeil1'];
$destinationAddress2 = $cfgArr['AddresseTeil2'];

$positionNames = [];
$c = 1;
foreach ($cfgArr['Positionen'] as $Positionsname) {
    $positionNames[] = $Positionsname;
    $positionSums[] = $argv[$c];
    $c++;
}

$invoiceNumber = $now->format('Y-m-') . $cfgArr['Rechnungsnummer'];

//manipulate input
if ($anrede == 'Herr') {
    $anrede = 'r Herr';
} else {
    $anrede = ' ' . $anrede;
}

$tableBody = '';
$total = 0;
$body = '\\\\';
foreach ($positionNames as $key => $name) {
    $body .= $name . ' &  &  &  & \multicolumn{1}{r}{' . number_format($positionSums[$key], 2, ',', ' ') . ' EUR} \\\\ ';
    $total += $positionSums[$key];
}
$body .= '\\\\ \hline ';
$body .= '\multicolumn{ 4}{l}{ \textbf{Gesamtsumme} } & \textbf{' . number_format($total, 2, ',', ' ') . ' EUR} \\\\ \hline ';

$data = '%---------------------------------------------------------------------------
\documentclass%%
%---------------------------------------------------------------------------
  [fontsize=11pt,%%          Schriftgroesse
%---------------------------------------------------------------------------
% Satzspiegel
   paper=a4,%%               Papierformat
   %enlargefirstpage=on,%%    Erste Seite anders
   %pagenumber=headright,%%   Seitenzahl oben mittig
%---------------------------------------------------------------------------
% Layout
   headsepline=off,%%         Linie unter der Seitenzahl
   parskip=half,%%           Abstand zwischen Absaetzen
%---------------------------------------------------------------------------
% Was kommt in den Briefkopf und in die Anschrift
   fromalign=right,%%        Plazierung des Briefkopfs
   fromphone=on,%%           Telefonnummer im Absender
   fromrule=aftername,%%     Linie im Absender (aftername, afteraddress)
   fromfax=off,%%            Faxnummer
   fromemail=on,%%           Emailadresse
   fromurl=off,%%            Homepage
   fromlogo=on,%%            Firmenlogo
   addrfield=on,%%           Adressfeld fuer Fensterkuverts
   backaddress=on,%%         ...und Absender im Fenster
   subject=beforeopening,%%  Plazierung der Betreffzeile
   locfield=narrow,%%        zusaetzliches Feld fuer Absender
   foldmarks=on,%%           Faltmarken setzen
   numericaldate=off,%%      Datum numerisch ausgeben
   refline=narrow,%%         Geschaeftszeile im Satzspiegel
   firstfoot=on,%%           Footerbereich
%---------------------------------------------------------------------------
% Formatierung
   draft=off%%                Entwurfsmodus
]{scrlttr2}
%---------------------------------------------------------------------------
\usepackage[english, ngerman]{babel}
\usepackage{url}
\usepackage{lmodern}
\usepackage[utf8]{inputenc}
\usepackage{tabularx}
\usepackage{colortbl}
% symbols: (cell)phone, email
\RequirePackage{marvosym} % for gray color in header
%\RequirePackage{color} % for gray color in header
\usepackage[T1]{fontenc}
%---------------------------------------------------------------------------
% Schriften werden hier definiert
\renewcommand*\familydefault{\sfdefault} % Latin Modern Sans
\setkomafont{fromname}{\sffamily\color{mygray}\LARGE}
%\setkomafont{pagenumber}{\sffamily}
\setkomafont{subject}{\mdseries}
\setkomafont{backaddress}{\mdseries}
\setkomafont{fromaddress}{\small\sffamily\mdseries\color{mygray}}
%---------------------------------------------------------------------------
\begin{document}
%---------------------------------------------------------------------------
% Briefstil und Position des Briefkopfs
\LoadLetterOption{DIN} %% oder: DINmtext, SN, SNleft, KOMAold.
\makeatletter
\@setplength{sigbeforevskip}{17mm} % Abstand der Signatur von dem closing
\@setplength{firstheadvpos}{17mm} % Abstand des Absenderfeldes vom Top
\@setplength{firstfootvpos}{275mm} % Abstand des Footers von oben
\@setplength{firstheadwidth}{\paperwidth}
\@setplength{locwidth}{70mm}   % Breite des Locationfeldes
\@setplength{locvpos}{65mm}    % Abstand des Locationfeldes von oben
\ifdim \useplength{toaddrhpos}>\z@
  \@addtoplength[-2]{firstheadwidth}{\useplength{toaddrhpos}}
\else
  \@addtoplength[2]{firstheadwidth}{\useplength{toaddrhpos}}
\fi
\@setplength{foldmarkhpos}{6.5mm}
\makeatother
%---------------------------------------------------------------------------
% Farben werden hier definiert
% define gray for header
\definecolor{mygray}{gray}{.55}
% define blue for address
\definecolor{myblue}{rgb}{0.25,0.45,0.75}

%---------------------------------------------------------------------------
% Absender Daten
\setkomavar{fromname}{' . $persDataArr['Vorname'] . ' ' . $persDataArr['Nachname'] . '}
\setkomavar{fromaddress}{' . $persDataArr['AddresseTeil1'] . '\\\\' . $persDataArr['AddresseTeil2'] . '}
\setkomavar{fromphone}[\Mobilefone~]{' . $persDataArr['Telefon'] .  '}
%\setkomavar{fromfax}[\FAX~]{+49\,(0)\,123\,456\,789\,0}
\setkomavar{fromemail}[\Letter~]{'. $persDataArr['eMail'] .'}
%\setkomavar{fromurl}[]{http://max-mustermann.de}
%\setkomafont{fromaddress}{\small\rmfamily\mdseries\slshape\color{myblue}}
\newkomavar[Steuernummer]{stnr}
\setkomavar{stnr}{' . $persDataArr['Steuernummer'] . '}

\setkomavar{backaddressseparator}{ - }
%\setkomavar{backaddress}{Max Mustermann, alternative Straße, alternative Stadt} % wenn erwünscht kann hier eine andere Backaddress eingetragen werden
\setkomavar{signature}{' . $persDataArr['Vorname'] . ' ' . $persDataArr['Nachname'] . '}
% signature same indention level as rest
\renewcommand*{\raggedsignature}{\raggedright}
\setkomavar{location}{\raggedleft

}
% Anlage neu definieren
\renewcommand{\enclname}{Anlagen}
\setkomavar{enclseparator}{: }
%---------------------------------------------------------------------------
% Seitenstil
%pagenumber=footmiddle
\pagestyle{plain}%% keine Header in der Kopfzeile bzw. plain
\pagenumbering{arabic}
%---------------------------------------------------------------------------
%---------------------------------------------------------------------------
\firstfoot{\footnotesize%
\rule[3pt]{\textwidth}{.4pt} \\\\
\begin{tabular}[t]{l@{}}%
\usekomavar{fromname}\\\\
\usekomavar{fromaddress}\\\\
\end{tabular}%
\hfill
\begin{tabular}[t]{l@{}}%
  \usekomavar[\Mobilefone~]{fromphone}\\\\
   \usekomavar[\Letter~]{fromemail}\\\\
    {Steuernummer}: \usekomavar{stnr}\\\\
\end{tabular}%
\ifkomavarempty{frombank}{}{%
\hfill
\begin{tabular}[t]{l@{}}%
Bankverbindung: \\\\
\usekomavar{frombank}
\end{tabular}%
}%
}%
%---------------------------------------------------------------------------
% Bankverbindung
\setkomavar{frombank}{IBAN: ' . $persDataArr['IBAN'] . '\\\\
BIC: ' . $persDataArr['BIC'] . '\\\\
' . $persDataArr['Bankname'] . '}
%---------------------------------------------------------------------------
%\setkomavar{yourref}{}
%\setkomavar{yourmail}{}
%\setkomavar{myref}{}
%\setkomavar{customer}{}
\setkomavar{invoice}{'. $invoiceNumber .'}
%---------------------------------------------------------------------------
% Datum und Ort werden hier eingetragen
\setkomavar{date}{\today}
\setkomavar{place}{Chemnitz}
%---------------------------------------------------------------------------

%---------------------------------------------------------------------------
% Hier beginnt der Brief, mit der Anschrift des Empfängers

\begin{letter}
{
'. $destinationFirstName . ' ' . $destinationLastName .'\\\\
'. $destinationAddress1 .'\\\\
'. $destinationAddress2 .'\\\\
}
%---------------------------------------------------------------------------
% Der Betreff des Briefes
\setkomavar{subject}{\bf{RECHNUNG
}
}
%---------------------------------------------------------------------------
\opening{Sehr geehrte'. $anrede .' '. $destinationLastName . ',}

Bitte überweisen Sie den folgenden Rechnungsbetrag innerhalb von 14 Tagen auf das unten angegebene Konto.

\vspace{5pt}
\begin{tabularx}{\textwidth}{lcXrr}
\hline
%\rowcolor[gray]{.95}
\tiny {Beschreibung} & \tiny {} & \tiny {} & \tiny {} & \tiny {Gesamtpreis (netto)} \\\\ \hline
' . $body . '
\end{tabularx}\\\\
\\\\
\\\\ (Das Datum der Rechnung entspricht dem Leistungsdatum)\\\\
Als Kleinunternehmer im Sinne von § 19 Abs. 1 UStG wird Umsatzsteuer nicht berechnet!

Vielen Dank für Ihren Auftrag!

\closing{Mit freundlichen Grüßen,}
%---------------------------------------------------------------------------
%\ps{PS:}
%\cc{}
%---------------------------------------------------------------------------
\end{letter}
%---------------------------------------------------------------------------
\end{document}
%---------------------------------------------------------------------------';


file_put_contents($filename, $data);

system('/usr/bin/pdflatex ' . $filename);

$pdfName = str_replace('.tex', '.pdf', $filename);
system('/usr/bin/firefox ' . $pdfName);
