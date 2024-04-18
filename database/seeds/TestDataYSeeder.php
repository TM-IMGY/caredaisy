<?php

use Illuminate\Database\Seeder;

class TestDataYSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $timestamp = new DateTime();
      // i_corporationsにデータを挿入する
      DB::table('i_corporations')->insert([
        'name' => 'ことり法人',
        'abbreviation' => 'ことり法人',
        'representative' => '田中一郎',
        'phone_number' => '0465-35-2856',
        'fax_number' => '',
        'postal_code' => '250-0002',
        'location' => '神奈川県小田原市寿町４－１４－１９',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      // i_institutionsにデータを挿入する
      DB::table('i_institutions')->insert([
        'corporation_id' => 1,
        'name' => 'ひまわり施設',
        'abbreviation' => 'ひまわり施設',
        'representative' => '田中太郎',
        'phone_number' => '0465-35-2856',
        'fax_number' => '0465-35-5130',
        'postal_code' => '250-0002',
        'location' => '神奈川県小田原市寿町４－１４－１９',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_institutions')->insert([
        'corporation_id' => 2,
        'name' => 'ことり施設',
        'abbreviation' => 'ことり施設',
        'representative' => '田中一郎',
        'phone_number' => '0465-35-2856',
        'fax_number' => '0465-35-5130',
        'postal_code' => '250-0002',
        'location' => '神奈川県小田原市寿町４－１４－１９',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      // i_facilitiesにデータを挿入する
      DB::table('i_facilities')->insert([
        'facility_number' => '1472301079',
        'facility_name_kanji' => 'ひまわり事業所',
        'facility_name_kana' => 'ひまわり事業所',
        'insurer_no' => '140079',
        'area' => '5',
        'postal_code' => '250-0002',
        'location' => '神奈川県小田原市寿町４－１４－１９',
        'phone_number' => '0465-35-2856',
        'fax_number' => '0465-35-5130',
        'remarks' => '',
        'invalid_flag' => '0',
        'abbreviation' => 'ひまわり事業所',
        'institution_id' => 2,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_facilities')->insert([
        'facility_number' => '1472301027',
        'facility_name_kanji' => 'チューリップ事業所',
        'facility_name_kana' => 'チューリップ事業所',
        'insurer_no' => '140079',
        'area' => '5',
        'postal_code' => '250-0002',
        'location' => '神奈川県小田原市寿町４－１４－１９',
        'phone_number' => '0465-35-2856',
        'fax_number' => '0465-35-5130',
        'remarks' => '',
        'invalid_flag' => '0',
        'abbreviation' => 'チューリップ事業所',
        'institution_id' => 2,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_facilities')->insert([
        'facility_number' => '1472301028',
        'facility_name_kanji' => 'さくら事業所',
        'facility_name_kana' => 'さくら事業所',
        'insurer_no' => '140079',
        'area' => '5',
        'postal_code' => '250-0002',
        'location' => '神奈川県小田原市寿町４－１４－１９',
        'phone_number' => '0465-35-2856',
        'fax_number' => '0465-35-5130',
        'remarks' => '',
        'invalid_flag' => '0',
        'abbreviation' => 'さくら事業所',
        'institution_id' => 2,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_facilities')->insert([
        'facility_number' => '1472301029',
        'facility_name_kanji' => 'ことり事業所',
        'facility_name_kana' => 'ことり事業所',
        'insurer_no' => '140079',
        'area' => '5',
        'postal_code' => '250-0002',
        'location' => '神奈川県小田原市寿町４－１４－１９',
        'phone_number' => '0465-35-2856',
        'fax_number' => '0465-35-5130',
        'remarks' => '',
        'invalid_flag' => '0',
        'abbreviation' => 'ひまわり事業所',
        'institution_id' => 3,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_facilities')->insert([
        'facility_number' => '1472301030',
        'facility_name_kanji' => 'うぐいす事業所',
        'facility_name_kana' => 'うぐいす事業所',
        'insurer_no' => '140079',
        'area' => '5',
        'postal_code' => '250-0002',
        'location' => '神奈川県小田原市寿町４－１４－１９',
        'phone_number' => '0465-35-2856',
        'fax_number' => '0465-35-5130',
        'remarks' => '',
        'invalid_flag' => '0',
        'abbreviation' => 'うぐいす事業所',
        'institution_id' => 3,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_facilities')->insert([
        'facility_number' => '1472301031',
        'facility_name_kanji' => 'スズメ事業所',
        'facility_name_kana' => 'スズメ事業所',
        'insurer_no' => '140079',
        'area' => '5',
        'postal_code' => '250-0002',
        'location' => '神奈川県小田原市寿町４－１４－１９',
        'phone_number' => '0465-35-2856',
        'fax_number' => '0465-35-5130',
        'remarks' => '',
        'invalid_flag' => '0',
        'abbreviation' => 'スズメ事業所',
        'institution_id' => 3,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      // corporation_account
      DB::table('corporation_account')->insert([
        'account_id' => 1,
        'corporation_id' => 1,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('corporation_account')->insert([
        'account_id' => 1,
        'corporation_id' => 2,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      // i_services
      DB::table('i_services')->insert([
        'facility_id' => '2',
        'service_type_code_id' => '1',
        'area' => '5',
        'change_date' => '2021/9/24',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_services')->insert([
        'facility_id' => '3',
        'service_type_code_id' => '1',
        'area' => '5',
        'change_date' => '2021/9/24',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_services')->insert([
        'facility_id' => '4',
        'service_type_code_id' => '1',
        'area' => '5',
        'change_date' => '2021/9/24',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_services')->insert([
        'facility_id' => '2',
        'service_type_code_id' => '2',
        'area' => '5',
        'change_date' => '2021/9/24',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_services')->insert([
        'facility_id' => '3',
        'service_type_code_id' => '2',
        'area' => '5',
        'change_date' => '2021/9/24',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_services')->insert([
        'facility_id' => '4',
        'service_type_code_id' => '2',
        'area' => '5',
        'change_date' => '2021/9/24',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_services')->insert([
        'facility_id' => '5',
        'service_type_code_id' => '1',
        'area' => '5',
        'change_date' => '2021/9/24',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_services')->insert([
        'facility_id' => '6',
        'service_type_code_id' => '1',
        'area' => '5',
        'change_date' => '2021/9/24',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);

      DB::table('i_services')->insert([
        'facility_id' => '7',
        'service_type_code_id' => '1',
        'area' => '5',
        'change_date' => '2021/9/24',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
      ]);
    }
}
