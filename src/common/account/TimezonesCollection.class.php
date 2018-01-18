<?php
/**
 * Copyright (c) Enalean, 2014-2018. All Rights Reserved.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

class Account_TimezonesCollection {

    private $timezones = array(
        'US/Alaska',
        'US/Aleutian',
        'US/Arizona',
        'US/Central',
        'US/Eastern',
        'US/East-Indiana',
        'US/Hawaii',
        'US/Indiana-Starke',
        'US/Michigan',
        'US/Mountain',
        'US/Pacific',
        'US/Samoa',
        'Africa/Abidjan',
        'Africa/Accra',
        'Africa/Addis_Ababa',
        'Africa/Algiers',
        'Africa/Asmera',
        'Africa/Bamako',
        'Africa/Bangui',
        'Africa/Banjul',
        'Africa/Bissau',
        'Africa/Blantyre',
        'Africa/Brazzaville',
        'Africa/Bujumbura',
        'Africa/Cairo',
        'Africa/Casablanca',
        'Africa/Ceuta',
        'Africa/Conakry',
        'Africa/Dakar',
        'Africa/Dar_es_Salaam',
        'Africa/Djibouti',
        'Africa/Douala',
        'Africa/El_Aaiun',
        'Africa/Freetown',
        'Africa/Gaborone',
        'Africa/Harare',
        'Africa/Johannesburg',
        'Africa/Kampala',
        'Africa/Khartoum',
        'Africa/Kigali',
        'Africa/Kinshasa',
        'Africa/Lagos',
        'Africa/Libreville',
        'Africa/Lome',
        'Africa/Luanda',
        'Africa/Lubumbashi',
        'Africa/Lusaka',
        'Africa/Malabo',
        'Africa/Maputo',
        'Africa/Maseru',
        'Africa/Mbabane',
        'Africa/Mogadishu',
        'Africa/Monrovia',
        'Africa/Nairobi',
        'Africa/Ndjamena',
        'Africa/Niamey',
        'Africa/Nouakchott',
        'Africa/Ouagadougou',
        'Africa/Porto-Novo',
        'Africa/Sao_Tome',
        'Africa/Timbuktu',
        'Africa/Tripoli',
        'Africa/Tunis',
        'Africa/Windhoek',
        'America/Adak',
        'America/Anchorage',
        'America/Anguilla',
        'America/Antigua',
        'America/Araguaina',
        'America/Aruba',
        'America/Asuncion',
        'America/Atka',
        'America/Barbados',
        'America/Belem',
        'America/Belize',
        'America/Boa_Vista',
        'America/Bogota',
        'America/Boise',
        'America/Buenos_Aires',
        'America/Cambridge_Bay',
        'America/Cancun',
        'America/Caracas',
        'America/Catamarca',
        'America/Cayenne',
        'America/Cayman',
        'America/Chicago',
        'America/Chihuahua',
        'America/Cordoba',
        'America/Costa_Rica',
        'America/Cuiaba',
        'America/Curacao',
        'America/Dawson',
        'America/Dawson_Creek',
        'America/Denver',
        'America/Detroit',
        'America/Dominica',
        'America/Edmonton',
        'America/El_Salvador',
        'America/Ensenada',
        'America/Fortaleza',
        'America/Fort_Wayne',
        'America/Glace_Bay',
        'America/Godthab',
        'America/Goose_Bay',
        'America/Grand_Turk',
        'America/Grenada',
        'America/Guadeloupe',
        'America/Guatemala',
        'America/Guayaquil',
        'America/Guyana',
        'America/Halifax',
        'America/Havana',
        'America/Hermosillo',
        'America/Indiana/Indianapolis',
        'America/Indiana/Knox',
        'America/Indiana/Marengo',
        'America/Indiana/Vevay',
        'America/Indianapolis',
        'America/Inuvik',
        'America/Iqaluit',
        'America/Jamaica',
        'America/Jujuy',
        'America/Juneau',
        'America/Knox_IN',
        'America/La_Paz',
        'America/Lima',
        'America/Los_Angeles',
        'America/Louisville',
        'America/Maceio',
        'America/Managua',
        'America/Manaus',
        'America/Martinique',
        'America/Mazatlan',
        'America/Mendoza',
        'America/Menominee',
        'America/Mexico_City',
        'America/Miquelon',
        'America/Montevideo',
        'America/Montreal',
        'America/Montserrat',
        'America/Nassau',
        'America/New_York',
        'America/Nipigon',
        'America/Nome',
        'America/Noronha',
        'America/Panama',
        'America/Pangnirtung',
        'America/Paramaribo',
        'America/Phoenix',
        'America/Port-au-Prince',
        'America/Porto_Acre',
        'America/Port_of_Spain',
        'America/Porto_Velho',
        'America/Puerto_Rico',
        'America/Rainy_River',
        'America/Rankin_Inlet',
        'America/Regina',
        'America/Rosario',
        'America/Santiago',
        'America/Santo_Domingo',
        'America/Sao_Paulo',
        'America/Scoresbysund',
        'America/Shiprock',
        'America/St_Johns',
        'America/St_Kitts',
        'America/St_Lucia',
        'America/St_Thomas',
        'America/St_Vincent',
        'America/Swift_Current',
        'America/Tegucigalpa',
        'America/Thule',
        'America/Thunder_Bay',
        'America/Tijuana',
        'America/Tortola',
        'America/Vancouver',
        'America/Virgin',
        'America/Whitehorse',
        'America/Winnipeg',
        'America/Yakutat',
        'America/Yellowknife',
        'Antarctica/Casey',
        'Antarctica/Davis',
        'Antarctica/DumontDUrville',
        'Antarctica/Mawson',
        'Antarctica/McMurdo',
        'Antarctica/Palmer',
        'Antarctica/South_Pole',
        'Antarctica/Syowa',
        'Arctic/Longyearbyen',
        'Asia/Aden',
        'Asia/Almaty',
        'Asia/Amman',
        'Asia/Anadyr',
        'Asia/Aqtau',
        'Asia/Aqtobe',
        'Asia/Ashkhabad',
        'Asia/Baghdad',
        'Asia/Bahrain',
        'Asia/Baku',
        'Asia/Bangkok',
        'Asia/Beirut',
        'Asia/Bishkek',
        'Asia/Brunei',
        'Asia/Calcutta',
        'Asia/Chungking',
        'Asia/Colombo',
        'Asia/Dacca',
        'Asia/Damascus',
        'Asia/Dili',
        'Asia/Dubai',
        'Asia/Dushanbe',
        'Asia/Gaza',
        'Asia/Harbin',
        'Asia/Hong_Kong',
        'Asia/Hovd',
        'Asia/Irkutsk',
        'Asia/Istanbul',
        'Asia/Jakarta',
        'Asia/Jayapura',
        'Asia/Jerusalem',
        'Asia/Kabul',
        'Asia/Kamchatka',
        'Asia/Karachi',
        'Asia/Kashgar',
        'Asia/Katmandu',
        'Asia/Krasnoyarsk',
        'Asia/Kuala_Lumpur',
        'Asia/Kuching',
        'Asia/Kuwait',
        'Asia/Macao',
        'Asia/Magadan',
        'Asia/Manila',
        'Asia/Muscat',
        'Asia/Nicosia',
        'Asia/Novosibirsk',
        'Asia/Omsk',
        'Asia/Phnom_Penh',
        'Asia/Pyongyang',
        'Asia/Qatar',
        'Asia/Rangoon',
        'Asia/Riyadh',
        'Asia/Saigon',
        'Asia/Samarkand',
        'Asia/Seoul',
        'Asia/Shanghai',
        'Asia/Singapore',
        'Asia/Taipei',
        'Asia/Tashkent',
        'Asia/Tbilisi',
        'Asia/Tehran',
        'Asia/Tel_Aviv',
        'Asia/Thimbu',
        'Asia/Tokyo',
        'Asia/Ujung_Pandang',
        'Asia/Ulaanbaatar',
        'Asia/Ulan_Bator',
        'Asia/Urumqi',
        'Asia/Vientiane',
        'Asia/Vladivostok',
        'Asia/Yakutsk',
        'Asia/Yekaterinburg',
        'Asia/Yerevan',
        'Atlantic/Azores',
        'Atlantic/Bermuda',
        'Atlantic/Canary',
        'Atlantic/Cape_Verde',
        'Atlantic/Faeroe',
        'Atlantic/Jan_Mayen',
        'Atlantic/Madeira',
        'Atlantic/Reykjavik',
        'Atlantic/South_Georgia',
        'Atlantic/Stanley',
        'Atlantic/St_Helena',
        'Australia/ACT',
        'Australia/Adelaide',
        'Australia/Brisbane',
        'Australia/Broken_Hill',
        'Australia/Canberra',
        'Australia/Darwin',
        'Australia/Hobart',
        'Australia/LHI',
        'Australia/Lindeman',
        'Australia/Lord_Howe',
        'Australia/Melbourne',
        'Australia/North',
        'Australia/NSW',
        'Australia/Perth',
        'Australia/Queensland',
        'Australia/South',
        'Australia/Sydney',
        'Australia/Tasmania',
        'Australia/Victoria',
        'Australia/West',
        'Australia/Yancowinna',
        'Brazil/Acre',
        'Brazil/DeNoronha',
        'Brazil/East',
        'Brazil/West',
        'Canada/Atlantic',
        'Canada/Central',
        'Canada/Eastern',
        'Canada/Mountain',
        'Canada/Newfoundland',
        'Canada/Pacific',
        'Canada/Saskatchewan',
        'Canada/Yukon',
        'CET',
        'Chile/Continental',
        'Chile/EasterIsland',
        'CST6CDT',
        'Cuba',
        'EET',
        'Egypt',
        'Eire',
        'EST',
        'EST5EDT',
        'Europe/Amsterdam',
        'Europe/Andorra',
        'Europe/Athens',
        'Europe/Belfast',
        'Europe/Belgrade',
        'Europe/Berlin',
        'Europe/Bratislava',
        'Europe/Brussels',
        'Europe/Bucharest',
        'Europe/Budapest',
        'Europe/Chisinau',
        'Europe/Copenhagen',
        'Europe/Dublin',
        'Europe/Gibraltar',
        'Europe/Helsinki',
        'Europe/Istanbul',
        'Europe/Kaliningrad',
        'Europe/Kiev',
        'Europe/Lisbon',
        'Europe/Ljubljana',
        'Europe/London',
        'Europe/Luxembourg',
        'Europe/Madrid',
        'Europe/Malta',
        'Europe/Minsk',
        'Europe/Monaco',
        'Europe/Moscow',
        'Europe/Oslo',
        'Europe/Paris',
        'Europe/Prague',
        'Europe/Riga',
        'Europe/Rome',
        'Europe/Samara',
        'Europe/San_Marino',
        'Europe/Sarajevo',
        'Europe/Simferopol',
        'Europe/Skopje',
        'Europe/Sofia',
        'Europe/Stockholm',
        'Europe/Tallinn',
        'Europe/Tirane',
        'Europe/Tiraspol',
        'Europe/Uzhgorod',
        'Europe/Vaduz',
        'Europe/Vatican',
        'Europe/Vienna',
        'Europe/Vilnius',
        'Europe/Warsaw',
        'Europe/Zagreb',
        'Europe/Zaporozhye',
        'Europe/Zurich',
        'GB',
        'GB-Eire',
        'GMT',
        'GMT0',
        'GMT-0',
        'GMT+0',
        'Greenwich',
        'Hongkong',
        'HST',
        'Iceland',
        'Indian/Antananarivo',
        'Indian/Chagos',
        'Indian/Christmas',
        'Indian/Cocos',
        'Indian/Comoro',
        'Indian/Kerguelen',
        'Indian/Mahe',
        'Indian/Maldives',
        'Indian/Mauritius',
        'Indian/Mayotte',
        'Indian/Reunion',
        'Iran',
        'Israel',
        'Jamaica',
        'Japan',
        'Kwajalein',
        'Libya',
        'MET',
        'Mexico/BajaNorte',
        'Mexico/BajaSur',
        'Mexico/General',
        'MST',
        'MST7MDT',
        'Navajo',
        'NZ',
        'NZ-CHAT',
        'Pacific/Apia',
        'Pacific/Auckland',
        'Pacific/Chatham',
        'Pacific/Easter',
        'Pacific/Efate',
        'Pacific/Enderbury',
        'Pacific/Fakaofo',
        'Pacific/Fiji',
        'Pacific/Funafuti',
        'Pacific/Galapagos',
        'Pacific/Gambier',
        'Pacific/Guadalcanal',
        'Pacific/Guam',
        'Pacific/Honolulu',
        'Pacific/Johnston',
        'Pacific/Kiritimati',
        'Pacific/Kosrae',
        'Pacific/Kwajalein',
        'Pacific/Majuro',
        'Pacific/Marquesas',
        'Pacific/Midway',
        'Pacific/Nauru',
        'Pacific/Niue',
        'Pacific/Norfolk',
        'Pacific/Noumea',
        'Pacific/Pago_Pago',
        'Pacific/Palau',
        'Pacific/Pitcairn',
        'Pacific/Ponape',
        'Pacific/Port_Moresby',
        'Pacific/Rarotonga',
        'Pacific/Saipan',
        'Pacific/Samoa',
        'Pacific/Tahiti',
        'Pacific/Tarawa',
        'Pacific/Tongatapu',
        'Pacific/Truk',
        'Pacific/Wake',
        'Pacific/Wallis',
        'Pacific/Yap',
        'Poland',
        'Portugal',
        'PRC',
        'PST8PDT',
        'ROC',
        'ROK',
        'Singapore',
        'Turkey',
        'UCT',
        'Universal',
        'UTC',
        'WET',
        'W-SU',
        'Zulu',
    );

    public function getTimezones() {
        return $this->timezones;
    }

    public function isValidTimezone($timezone) {
        return in_array($timezone, $this->timezones);
    }

    public function getTimezonePresenters($current_timezone) {
        $list_of_presenters = array();
        foreach ($this->timezones as $timezone) {
            $presenter = new stdClass;
            $presenter->value = $timezone;
            $presenter->label = $timezone;
            $presenter->is_selected = $current_timezone === $timezone;

            $list_of_presenters[] = $presenter;
        }

        return $list_of_presenters;
    }

}