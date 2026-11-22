<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficialFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'title' => 'Application for Employment',
                'body' => '<div style="font-family:Arial,sans-serif; text-align:center; width:760px">
                        <p style="margin-left:80px"><span style="font-size:20px"><img alt="Sky Aviation Services Ltd." src="/images/logo/flydubai.jpg" style="height:100px; width:320px" /></span><img alt=" Please affix
                        a recent
                        stamp size
                        photograph her" src="https://cdn0.iconfinder.com/data/icons/business-blue-series-set-1-1/128/a-21-1024.png" style="float:right; height:130px; margin-right:10px; width:150px" /></p>

                        <p style="margin-left:80px">APPLICATION FOR EMPLOYMENT</p>

                        <p>To assist us in giving your application the fullest consideration, you are requested to complete this form in as much detail as possible.</p>

                        <p>&nbsp;</p>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; display:table; margin:auto; width:50%">
                            <tbody>
                                <tr>
                                    <td>Position Applied for</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <p>PLEASE COIVPLETE ALL THE DETAILS IN BLOCK LETTERS</p>

                        <p style="text-align:left"><span style="font-size:16px">Personal Details</span></p>

                        <p style="text-align:left">Please complete this section accurately as per your passport (Please attach a copy of your passport).</p>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; display:table; margin:auto; width:100%">
                            <tbody>
                                <tr>
                                    <td>
                                    <div style="float:left; text-align:left; width:49%">Surname: .................................................................................</div>

                                    <div style="float:left; text-align:left; width:49%">First Name:................................................................................</div>

                                    <div style="float:left; text-align:left; width:49%">Middle Name: ..........................................................................</div>

                                    <div style="float:left; text-align:left; width:49%">Sex (Male/Female):&nbsp;&nbsp;<input type="checkbox" />&nbsp;Male&nbsp;&nbsp; &nbsp;&nbsp;<input type="checkbox" /> Female</div>

                                    <div style="float:left; text-align:left; width:49%">Date of Birth: ............................................................................</div>

                                    <div style="float:left; text-align:left; width:49%">place of Birth (D/M/Y): ...............................................................</div>

                                    <div style="float:left; text-align:left; width:33%">Religion: ..................................................&nbsp;</div>

                                    <div style="float:left; text-align:left; width:33%">Blood Group: ...........................................</div>

                                    <div style="float:left; text-align:left; width:33%">Marital Status: ..........................................</div>

                                    <div style="float:left; text-align:left; width:99%">Mark of ldentification (lf any): ........................................................................................................................................................</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left">
                                    <div style="float:left; text-align:left; width:99%">Contact Address (Present) :&nbsp;..................................................</div>

                                    <div style="float:left; text-align:left; width:99%">................................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">.............................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">...............................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Business Contact: .................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Residence Contact: ...............................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Mobile:...................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Fax (lf Any):...........................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">E-mail:...................................................................................</div>

                                    <p style="text-align:left">&nbsp;</p>
                                    </td>
                                    <td style="text-align:left">
                                    <div style="float:left; text-align:left; width:99%">Permanent Address: ..................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">....................................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">....................................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">....................................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Business Contact :......................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Residence Contact: ...................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Mobile: ......................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Do you have a valid driving license ?&nbsp;<input type="checkbox" />&nbsp;Yes&nbsp;&nbsp; <input type="checkbox" /> No</div>

                                    <div style="float:left; text-align:left; width:99%">lf yes, license number: ..............................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Country: ....................................................................................</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left">
                                    <div style="float:left; text-align:left; width:99%">Nationality:&nbsp;.......................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Passport No: ....................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Residence Contact: ..........................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Date of lssue:....................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Date of Expiry:..................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Place of lssue:..................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">National l&#39;D Card No: .......................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Country of Residence: .....................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Colour of Hair/ Eyes: ........................................................</div>

                                    <div style="float:left; text-align:left; width:49%">Height: ...............................</div>

                                    <div style="float:left; text-align:left; width:49%">Weight:.................................</div>

                                    <div style="text-align:center">&nbsp;</div>

                                    <div style="text-align:center">&nbsp;</div>

                                    <div style="text-align:center">&nbsp;</div>

                                    <div style="text-align:center">&nbsp;</div>

                                    <div style="text-align:center">&nbsp;</div>

                                    <div style="float:left; text-align:center; width:99%">&nbsp; (Attach a Photo Copy of your Passport)</div>
                                    </td>
                                    <td style="text-align:lef">
                                    <div style="float:left; text-align:left; width:99%">lncase of Emergency Person to be Contacted</div>

                                    <div style="float:left; text-align:left; width:99%">Address: .......................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">........................................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">.......................................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">.......................................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Business Contact :.........................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Residence Contact: ......................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Mobile: .........................................................................................</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">NEXT OF KIN DETAILS</h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left">
                                    <div style="float:left; text-align:left; width:49%">Name:&nbsp;......................................................................................</div>

                                    <div style="float:left; text-align:left; width:49%">Relationship: ..............................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">Contact Address: ............................................................................................................................................................................</div>

                                    <div style="float:left; text-align:left; width:99%">.......................................................................................................................................................................................................</div>
                                    &nbsp;

                                    <div style="float:left; text-align:left; width:33%">Tel. Business: ..........................................</div>

                                    <div style="float:left; text-align:left; width:33%">Home: ......................................................</div>

                                    <div style="float:left; text-align:left; width:33%">Cell: ........................................................</div>

                                    <div style="float:left; text-align:left; width:49%">Fax (if Any): ..............................................................................</div>

                                    <div style="float:left; text-align:left; width:49%">E-mail: .......................................................................................</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">Family Details</h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="width:45%">Details of Father/Mother</td>
                                    <td style="width:20%">Date of Birth</td>
                                    <td style="width:20%">Place of Birth</td>
                                    <td style="width:15%">Passport No</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="width:45%">Details of Spouse &amp; Children</td>
                                    <td style="width:20%">Date of Birth</td>
                                    <td style="width:20%">Place of Birth</td>
                                    <td style="width:15%">Passport No</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <p style="text-align:left">lncase of more than one wife/husband name of spouse on the form shall be considered as lawful spouse</p>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="width:45%">Details of Brother &amp; Sisters</td>
                                    <td style="width:20%">Date of Birth</td>
                                    <td style="width:20%">Place of Birth</td>
                                    <td style="width:15%">Passport No</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">EDUCATIONAL QUALI FICATION&nbsp;<span style="font-size:12px">(Please attach copies of your certificates)</span></h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="width:15%">From</td>
                                    <td style="width:15%">To</td>
                                    <td style="width:45%">School / College / University</td>
                                    <td style="width:25%">Course Detail / Certificates</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">SPECIALIZED TRAINING &nbsp;<span style="font-size:12px">(Please attach copies of your certificates)</span></h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="width:15%">From</td>
                                    <td style="width:15%">To</td>
                                    <td style="width:45%">Institute</td>
                                    <td style="width:25%">Course Detail / Certificates</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">EMPLOYMENT HISTORY-&nbsp;<span style="font-size:12px">PIease start with your present Job and mention in the reverse chronological order the Jobs held</span></h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td colspan="2" style="width:26%">Date</td>
                                    <td rowspan="2" style="width:44%">Name and Address of Employer</td>
                                    <td rowspan="2" style="width:15%">Position</td>
                                    <td rowspan="2" style="width:15%">Salary</td>
                                </tr>
                                <tr>
                                    <td style="width:12%">From</td>
                                    <td style="width:12%">To</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">LANGUAGE PROFICIENCY-&nbsp;<span style="font-size:12px"> tick mark as appropriate</span></h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td rowspan="2" style="width:14%">Language</td>
                                    <td colspan="3" style="width:43%">Spoken</td>
                                    <td colspan="3" style="width:43%">Written</td>
                                </tr>
                                <tr>
                                    <td style="width:14%">Poor</td>
                                    <td style="width:14%">Average</td>
                                    <td style="width:14%">Fluent</td>
                                    <td style="width:14%">Poor</td>
                                    <td style="width:14%">Average</td>
                                    <td style="width:14%">Fluent</td>
                                </tr>
                                <tr>
                                    <td>English</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>Bangla</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">EXTRA CURRICULAR ACTIVITIES</h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td rowspan="3">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">Personal References &nbsp;<span style="font-size:12px"> [Please give at least 2 Business and 1 Personal References]</span></h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td colspan="2" style="text-align:left">1) Name and Address:</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">Telephone:</td>
                                    <td style="text-align:left">Designation/Company:</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:left">2) Name and Address:</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">Telephone:</td>
                                    <td style="text-align:left">Designation/Company:</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:left">3) Name and Address:</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">Telephone:</td>
                                    <td style="text-align:left">Designation/Company:</td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left">Do you have any close relatives working with Sky Air Services Ltd.&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<input type="checkbox" />Yes&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<input type="checkbox" />No</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">
                                    <p>If yes, please mention name, relationship to you &amp; department: ......................................................................................................</p>

                                    <p>...........................................................................................................................................................................................................</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">GENERAL INFORIIIATION</h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left">
                                    <p>Have you applied for a job with Sky Air Services Ltd. earlier ?&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" />Yes&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<input type="checkbox" />No</p>

                                    <p>If the answer is YES please give details: .........................................................................................................................................</p>

                                    <p>..........................................................................................................................................................................................................</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left">
                                    <p>Have you ever been convicted for any crime? If yes, please give details: .......................................................................................</p>

                                    <p>..........................................................................................................................................................................................................</p>

                                    <p>..........................................................................................................................................................................................................</p>

                                    <p>Have you ever worked or lived away from home?: .........................................................................................................................</p>

                                    <p>........................................................................................................................................................................................................</p>

                                    <p>Have you dealt with the general public in your previous jobs?: .....................................................................................................</p>

                                    <p>.......................................................................................................................................................................................................</p>

                                    <p>What Oo you like most about the jobs you have done?: ...............................................................................................................</p>

                                    <p>.......................................................................................................................................................................................................</p>

                                    <p>Have you flown anywhere? (Please give details): ........................................................................................................................</p>

                                    <p>......................................................................................................................................................................................................</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left">
                                    <p>If selected, how long notice period do you have to give to your present employer?: ....................................................................</p>

                                    <p>......................................................................................................................................................................................................</p>

                                    <p>Any other facts you wish to bring to our notice?: .........................................................................................................................</p>

                                    <p>.....................................................................................................................................................................................................</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left">
                                    <p>I declare that the information given is correct to the best of my knowledge and belief and that I have not withheld any information which might be considered adversely to affect my fitness for employment.</p>

                                    <p>&nbsp;</p>

                                    <div style="float:left; text-align:left; width:49%">Date:&nbsp;........................................................</div>

                                    <div style="float:left; text-align:left; width:49%">Signature: ..............................................................</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left">PLEASE DO NOT WRITE BELOW THIS LINE</h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left; width:49%">
                                    <p style="text-align:left">FOR OFFICIAL USE ONLY</p>

                                    <p style="text-align:left">Comments: ...............................................................................</p>

                                    <p style="text-align:left">.................................................................................................</p>

                                    <p style="text-align:left">.................................................................................................</p>

                                    <p style="text-align:left">.................................................................................................</p>

                                    <p style="text-align:left">.................................................................................................</p>

                                    <p style="text-align:left">.................................................................................................</p>

                                    <p style="text-align:left">.................................................................................................</p>

                                    <p style="text-align:left">.................................................................................................</p>

                                    <p style="text-align:left">.................................................................................................</p>

                                    <p style="text-align:left">&nbsp;</p>
                                    </td>
                                    <td style="width:49%">
                                    <table border="1" cellpadding="10" cellspacing="0" style="width:100%">
                                        <tbody>
                                            <tr>
                                                <td style="text-align:left; width:30%">ID No.</td>
                                                <td colspan="2" rowspan="1">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left">Date of Joining</td>
                                                <td colspan="2" rowspan="1">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left">Grade/Salary</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left">Department</td>
                                                <td colspan="2" rowspan="1">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left">Designation</td>
                                                <td colspan="2" rowspan="1">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left">Station / Location</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left">Probation Period</td>
                                                <td colspan="2" rowspan="1">&nbsp;</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <p>&nbsp;</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                    <div style="float:left; text-align:left; width:99%">&nbsp;</div>

                                    <div style="float:left; text-align:left; width:99%">&nbsp;</div>

                                    <div style="float:left; text-align:left; width:33%">Recommended by</div>

                                    <div style="float:left; text-align:left; width:33%">Human Resource</div>

                                    <div style="float:left; text-align:left; width:33%">Approved by Managing Director</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p style="text-align:left">&nbsp;</p>

                        <p style="text-align:left">&nbsp;</p>
                        </div>',
                'created_at' => now(),
                'updated_at'=> now(),
            ],
            [
                'title' => 'CONVEYANCE BILL',
                'body' => '<div style="width:790px">
                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td style="text-align:center"><img alt="" src="/images/logo/flydubai.jpg" style="height:100px; width:300px" /></td>
                                    </tr>
                                </tbody>
                            </table>

                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td>
                                        <p>Period: __________</p>

                                        <p>Currency: BDT</p>
                                        </td>
                                        <td style="text-align:center"><span style="font-size:16px"><strong>CONVEYANCE BILL</strong></span></td>
                                        <td>
                                        <p>DATE: _____________</p>

                                        <p>Station: _____________</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td>
                                        <p>&nbsp; Staff Name:________________________________________________&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Staff No : ________________________________________&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</p>

                                        <p>&nbsp; Designation: _______________________________________________&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Date of Joning: ___________________________________</p>

                                        <p>&nbsp; Department: _______________________________________________</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td style="text-align:center"><strong>S.N</strong></td>
                                        <td style="text-align:center"><strong>&nbsp;Date</strong></td>
                                        <td style="text-align:center"><strong>&nbsp;From</strong></td>
                                        <td style="text-align:center"><strong>&nbsp;To</strong></td>
                                        <td style="text-align:center"><strong>&nbsp;Mode of Transport</strong></td>
                                        <td style="text-align:center"><strong>Purpose</strong></td>
                                        <td style="text-align:center"><strong>Authorized by</strong></td>
                                        <td style="text-align:center"><strong>Amount</strong></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p>Amount in words: _______________________________________________________________________________</p>

                            <p>&nbsp;</p>

                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td>
                                        <p style="text-align:center">&nbsp;</p>

                                        <p style="text-align:center">&nbsp;</p>

                                        <p style="text-align:center"><strong>Staff Signature</strong></p>
                                        </td>
                                        <td style="text-align:center">
                                        <p>&nbsp;</p>

                                        <p>&nbsp;</p>

                                        <p><strong>Recommended by</strong></p>
                                        </td>
                                        <td style="text-align:center">
                                        <p>&nbsp;</p>

                                        <p>&nbsp;</p>

                                        <p><strong>Cheacked by</strong></p>
                                        </td>
                                        <td style="text-align:center">
                                        <p>&nbsp;</p>

                                        <p>&nbsp;</p>

                                        <p><strong>Finance Manager</strong></p>
                                        </td>
                                        <td style="text-align:center">
                                        <p>&nbsp;</p>

                                        <p>&nbsp;</p>

                                        <p><strong>Approved by</strong></p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <p>Note: All rate of transportetion in according to company policy.</p>

                            <div>&nbsp;</div>
                            </div>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'=> 'Check List',
                'body'=> '<div style="font-family:Arial,sans-serif; text-align:center; width:750px">
                        <p><strong><span style="font-size:20px"><img alt="Sky Aviation Services Ltd." src="/images/logo/flydubai.jpg" style="height:140px; width:370px" /></span></strong></p>

                        <h2><strong>Check List</strong></h2>

                        <p style="margin-right:15%; text-align:right"><strong>Dated:</strong></p>

                        <p style="text-align:left"><strong>Station:</strong></p>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left"><strong>SI. No.:</strong></td>
                                    <td style="text-align:left"><strong>Handover:</strong></td>
                                    <td style="text-align:left"><strong>Takeover:</strong></td>
                                    <td style="text-align:left"><strong>Name of Items:</strong></td>
                                    <td style="text-align:left"><strong>Remarks:</strong></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                    <td style="text-align:left">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <p>&nbsp;</p>

                        <p>&nbsp;</p>

                        <p>&nbsp;</p>

                        <table cellspacing="0" style="border-collapse:collapse; width:100%">
                            <tbody>
                                <tr>
                                    <td style="border-style:none; text-align:center"><strong>Signature of Handover</strong></td>
                                    <td style="border-style:none; text-align:center"><strong>Signature of Takeover</strong></td>
                                    <td style="border-style:none; text-align:center"><strong>Signature of HR</strong></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Duty Travel Expenditure Form',
                'body' => '<div style="width:790px">
                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td style="text-align:center"><img alt="" src="/images/logo/flydubai.jpg" style="height:100px; width:300px" /></td>
                                    </tr>
                                </tbody>
                            </table>

                            <p style="text-align:center"><strong><u><span style="font-size:22px"><span style="font-family:Comic Sans MS,cursive">Duty Travel Expenditure Form</span></span></u></strong></p>

                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td>
                                        <p>1. Name of Staff:_______________________&nbsp; &nbsp; &nbsp; 2. Staff ID: _________________________________&nbsp; &nbsp;3. DOJ:_____________________________________</p>

                                        <p>4. Designation: ________________________&nbsp; &nbsp; &nbsp; 5. Depertment: ______________________________&nbsp; 6. Location: _________________________________</p>

                                        <p>7. Station: ____________________________&nbsp; &nbsp; &nbsp; 8. Date of Travel: ____________________________&nbsp; 9. Return Date: ______________________________</p>

                                        <p>10. No of days: ________________________&nbsp; &nbsp; &nbsp; 11. Destination: _____________________________&nbsp;</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <p style="text-align:center"><u><span style="font-family:Comic Sans MS,cursive"><span style="font-size:22px"><strong>Statement</strong></span></span></u></p>

                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td style="text-align:center"><strong>SI No.</strong></td>
                                        <td style="text-align:center"><strong>Dated</strong></td>
                                        <td style="text-align:center"><strong>Despcription</strong></td>
                                        <td style="text-align:center"><strong>Quantity</strong></td>
                                        <td style="text-align:center"><strong>Rate</strong></td>
                                        <td style="text-align:center"><strong>Amount</strong></td>
                                        <td style="text-align:center"><strong>Total Amount</strong></td>
                                        <td style="text-align:center"><strong>Remarks</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" rowspan="3" style="text-align:center"><br />
                                        <strong>1</strong><br />
                                        &nbsp;</td>
                                        <td colspan="1" rowspan="3" style="text-align:center"><br />
                                        <br />
                                        &nbsp;</td>
                                        <td style="text-align:center"><strong>Breakfast</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Lunch</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Dinner</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" rowspan="3" style="text-align:center"><strong>2</strong></td>
                                        <td colspan="1" rowspan="3" style="text-align:center"><br />
                                        <br />
                                        &nbsp;</td>
                                        <td style="text-align:center">
                                        <p><strong>Breakfast</strong></p>

                                        <p>&nbsp;</p>
                                        </td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Lunch</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Dinner</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" rowspan="3" style="text-align:center"><br />
                                        <strong>3</strong><br />
                                        &nbsp;</td>
                                        <td colspan="1" rowspan="3" style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Breakfast</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Lunch</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Dinner</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" rowspan="3" style="text-align:center">4</td>
                                        <td colspan="1" rowspan="3" style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Breakfast</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Lunch</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Dinner</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" rowspan="3" style="text-align:center">5</td>
                                        <td colspan="1" rowspan="3" style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Breakfast</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Lunch</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Dinner</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" rowspan="3" style="text-align:center">6</td>
                                        <td colspan="1" rowspan="3" style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Breakfast</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Lunch</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Dinner</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" rowspan="3" style="text-align:center">7</td>
                                        <td colspan="1" rowspan="3" style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Breakfast</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Lunch</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Dinner</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" rowspan="1" style="text-align:center"><strong>Sub Total</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p style="text-align:center"><span style="font-family:Comic Sans MS,cursive"><span style="font-size:22px"><u><strong>Transportation</strong></u></span></span></p>

                            <p><u><strong>Mode of Transportation</strong></u></p>

                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td colspan="1" rowspan="2" style="text-align:center">SI N<strong>o.</strong></td>
                                        <td colspan="1" rowspan="2" style="text-align:center"><strong>Dated</strong></td>
                                        <td colspan="1" rowspan="2" style="text-align:center"><strong>Despcription</strong></td>
                                        <td colspan="2" rowspan="1" style="text-align:center"><strong>Tick (<input checked="checked" type="checkbox" />)</strong></td>
                                        <td colspan="1" rowspan="2" style="text-align:center"><strong>From</strong></td>
                                        <td colspan="1" rowspan="2" style="text-align:center"><strong>To</strong></td>
                                        <td colspan="1" rowspan="2" style="text-align:center"><strong>Total</strong></td>
                                        <td colspan="1" rowspan="2"><strong>Remarks</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>One Way</strong></td>
                                        <td style="text-align:center"><strong>Return</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Bus</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Train</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Plane</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Rickshaw</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>CNG</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Taxi</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center"><strong>Steamer</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td style="text-align:center">&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" rowspan="1" style="text-align:right"><strong>Sub Total&nbsp;</strong></td>
                                        <td colspan="2" rowspan="1" style="text-align:center">&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p style="text-align:center"><u><span style="font-family:Comic Sans MS,cursive"><strong><span style="font-size:22px">Accommodation</span></strong></span></u></p>

                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td style="text-align:center"><strong>SI No.</strong></td>
                                        <td style="text-align:center"><strong>Dated</strong></td>
                                        <td>
                                        <p style="text-align:center"><strong>Room rent</strong></p>

                                        <p style="text-align:center"><strong>(BDT)</strong></p>
                                        </td>
                                        <td style="text-align:center"><strong>From</strong></td>
                                        <td style="text-align:center"><strong>To</strong></td>
                                        <td style="text-align:center"><strong>Total days</strong></td>
                                        <td style="text-align:center"><strong>Total Amount</strong></td>
                                        <td style="text-align:center"><strong>Remarks</strong></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" rowspan="1" style="text-align:right"><strong>Sub Total&nbsp;</strong></td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p style="text-align:center"><span style="font-family:Comic Sans MS,cursive"><span style="font-size:22px"><u><strong>Summary</strong></u></span></span></p>

                            <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td style="text-align:center"><strong>Food Sub Total</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Transport Sub Total</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Accommodation Sub Total</strong></td>
                                        <td style="text-align:center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center"><strong>Grand Total</strong></td>
                                        <td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p>&nbsp;</p>

                            <p>&nbsp;</p>

                            <table align="center" border="0" cellpadding="0" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td style="text-align:center"><strong>Signature of Staff&nbsp;&nbsp;</strong></td>
                                        <td style="text-align:center"><strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;HR&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></td>
                                        <td style="text-align:center"><strong>&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;Finance&nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong></td>
                                        <td style="text-align:center"><strong>Approved of MD</strong></td>
                                    </tr>
                                </tbody>
                            </table>

                            <p>&nbsp;</p>
                            </div>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Employee Appraisal (AP) FORM',
                'body'=> '<table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                            <tbody>
                                <tr>
                                    <td style="text-align:center">
                                    <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                        <tbody>
                                            <tr>
                                                <td><img alt="" src="/images/logo/flydubai.jpg" style="height:100px; width:300px" /></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                        <caption>
                                        <p><strong><span style="font-size:18px">Employee Appraisal (AP) FORM</span></strong></p>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="2" rowspan="1" style="text-align:center"><strong>Appraisal Period For:</strong></td>
                                                    <td colspan="2" rowspan="1" style="text-align:center"><strong>To:&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="text-align:center"><strong>Personal Information</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Staff Name</strong><br />
                                                    &nbsp;</td>
                                                    <td style="text-align:center"><strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></td>
                                                    <td style="text-align:center"><strong>Staff </strong><br />
                                                    &nbsp;</td>
                                                    <td style="text-align:center"><strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Designation</strong><br />
                                                    &nbsp;</td>
                                                    <td style="text-align:center"><br />
                                                    &nbsp;</td>
                                                    <td style="text-align:center"><strong>DOJ</strong><br />
                                                    &nbsp;</td>
                                                    <td style="text-align:center"><br />
                                                    &nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Depertment</strong><br />
                                                    &nbsp;</td>
                                                    <td style="text-align:center"><br />
                                                    &nbsp;</td>
                                                    <td style="text-align:center"><strong>Last AP Date</strong><br />
                                                    &nbsp;</td>
                                                    <td style="text-align:center"><br />
                                                    &nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Station / Location</strong><br />
                                                    &nbsp;</td>
                                                    <td colspan="3" rowspan="1" style="text-align:center">&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <p>&nbsp;<span style="font-family:Comic Sans MS,cursive"><span style="font-size:16px"><strong>Section ONE</strong></span></span></p>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="2" style="text-align:center"><strong>GENERAL PERFORMANCE STANDARDS</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>1</strong></td>
                                                    <td style="text-align:center"><strong>Appearance (Drooming, Dress Code, Cleanness, Personal Hygene)</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Grooming is not good, does not follow proper dress code, cleanness and personal hygene is not up to the mark&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Grooming is good, follow proper dress code, cleanness and personal hygene is up to the mark&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Grooming is perfect, Regularly follows dress code, very concious, cleanness and personal hygiene is positively noticeable&nbsp;</strong><input type="checkbox" /></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Scale of Marking</strong></td>
                                                    <td style="text-align:center">01</td>
                                                    <td style="text-align:center">02</td>
                                                    <td style="text-align:center">03</td>
                                                    <td style="text-align:center">04</td>
                                                    <td style="text-align:center">05</td>
                                                    <td style="text-align:center">06</td>
                                                    <td style="text-align:center">07</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                    <p>&nbsp;</p>

                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>4</strong></td>
                                                    <td style="text-align:center"><strong>Productivity</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Worhs siovrrer than expected; work is of substandard consrstency anC tinreiiness&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Completes work on tirne, with consistency and efficiency; meets departnrenta! stanciards.&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Quickly compietes work, often ahead of schedule; efrectively prioritizes works; exceds Cepartmentai standards.&nbsp;</strong><input type="checkbox" /></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Scale of Marking</strong></td>
                                                    <td style="text-align:center">01</td>
                                                    <td style="text-align:center">02</td>
                                                    <td style="text-align:center">03</td>
                                                    <td style="text-align:center">04</td>
                                                    <td style="text-align:center">05</td>
                                                    <td style="text-align:center">06</td>
                                                    <td style="text-align:center">07</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                    <p>&nbsp;</p>

                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>5</strong></td>
                                                    <td style="text-align:center"><strong>Attendance</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Tries to come earlv but late attendance is Common&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Regurlar bpt needs improvement&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Always on time and Punctual</strong><input type="checkbox" /></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Scale of Marking</strong></td>
                                                    <td style="text-align:center">01</td>
                                                    <td style="text-align:center">02</td>
                                                    <td style="text-align:center">03</td>
                                                    <td style="text-align:center">04</td>
                                                    <td style="text-align:center">05</td>
                                                    <td style="text-align:center">06</td>
                                                    <td style="text-align:center">07</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                    <p>&nbsp;</p>

                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>6</strong></td>
                                                    <td style="text-align:center"><strong>Communication Skills</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Communication skills impair work performance.&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Possesses the required communication skills and is effective in the possition meets departmental standards.<input type="checkbox" /></strong></td>
                                                    <td><strong>Has excellent communication skills, very effective in verbal writen interactions.</strong><input type="checkbox" /></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Scale of Marking</strong></td>
                                                    <td style="text-align:center">01</td>
                                                    <td style="text-align:center">02</td>
                                                    <td style="text-align:center">03</td>
                                                    <td style="text-align:center">04</td>
                                                    <td style="text-align:center">05</td>
                                                    <td style="text-align:center">06</td>
                                                    <td style="text-align:center">07</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                    <p>&nbsp;</p>

                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <p>&nbsp;</p>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>7</strong></td>
                                                    <td style="text-align:center"><strong>Work Relationship / Behavior</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Has trouble getting along with other emploees, supervisors and Passengers (Public)​.​​​​​​&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Has a generally positive approach in assisting others, maintains effective working relationships, meets departmental standards.<input type="checkbox" /></strong></td>
                                                    <td><strong>Exceeds departmental standards, highly cooperative, works hard to promote positive work relationships.</strong><input type="checkbox" /></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Scale of Marking</strong></td>
                                                    <td style="text-align:center">01</td>
                                                    <td style="text-align:center">02</td>
                                                    <td style="text-align:center">03</td>
                                                    <td style="text-align:center">04</td>
                                                    <td style="text-align:center">05</td>
                                                    <td style="text-align:center">06</td>
                                                    <td style="text-align:center">07</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                    <p>&nbsp;</p>

                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>8</strong></td>
                                                    <td style="text-align:center"><strong>Safety, Health</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Fails to follow safety rules, not health concious​.​​​​​​&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Follows safety rules and meets departmental standards. Possesses good Health.<input type="checkbox" /></strong></td>
                                                    <td><strong>Exceeds departmental standards for safety maintain good health.</strong><input type="checkbox" /></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Scale of Marking</strong></td>
                                                    <td style="text-align:center">01</td>
                                                    <td style="text-align:center">02</td>
                                                    <td style="text-align:center">03</td>
                                                    <td style="text-align:center">04</td>
                                                    <td style="text-align:center">05</td>
                                                    <td style="text-align:center">06</td>
                                                    <td style="text-align:center">07</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                    <p>&nbsp;</p>

                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <p>&nbsp;</p>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>9</strong></td>
                                                    <td style="text-align:center"><strong>Work Supervision / Independability</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Often fails to perform his/her duty shirks responsiblity, and very poor in supervisory control​.​​​​​​&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Conscrentious and steady worker; has a good record of dependabiiity. Always possesses the ability to supervise.<input type="checkbox" /></strong></td>
                                                    <td><strong>Very conscientious and dependable in the per{ormance of hisiher job. Extremely proficient in supevision and control.</strong><input type="checkbox" /></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Scale of Marking</strong></td>
                                                    <td style="text-align:center">01</td>
                                                    <td style="text-align:center">02</td>
                                                    <td style="text-align:center">03</td>
                                                    <td style="text-align:center">04</td>
                                                    <td style="text-align:center">05</td>
                                                    <td style="text-align:center">06</td>
                                                    <td style="text-align:center">07</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                    <p>&nbsp;</p>

                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>10</strong></td>
                                                    <td style="text-align:center"><strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Job Ethics&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Like to work independly not a team player​.​​​​​​&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Likes to works as member of a team (Good Team Player).<input type="checkbox" /></strong></td>
                                                    <td><strong>Possesses the quiality to work independaly ans as a team member.</strong><input type="checkbox" /></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Scale of Marking</strong></td>
                                                    <td style="text-align:center">01</td>
                                                    <td style="text-align:center">02</td>
                                                    <td style="text-align:center">03</td>
                                                    <td style="text-align:center">04</td>
                                                    <td style="text-align:center">05</td>
                                                    <td style="text-align:center">06</td>
                                                    <td style="text-align:center">07</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                    <p>&nbsp;</p>

                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="4" style="text-align:center"><strong>Section TWO</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">
                                                    <p><strong>BELOW STANDARD (BS): Job performance generally fails below standards required for the position.&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<input type="checkbox" /></strong></p>

                                                    <p><strong>MEETS STANDARDS (MS) : Job performance satisfactorily meed the requirements for the position.&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong><strong><input type="checkbox" /></strong></p>

                                                    <p><strong>EXCEEDS STANDARDS (ES): Job performance consistently exceeds the standards for the position&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<input type="checkbox" /></strong></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" rowspan="1" style="text-align:center"><strong>Job Descriptions / Performance</strong></td>
                                                    <td style="text-align:center"><strong>Satisfactoroy</strong></td>
                                                    <td style="text-align:center"><strong>not</strong>&nbsp;<strong>Satisfactory</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>1</strong></td>
                                                    <td style="text-align:center"><strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>2</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>3</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>4</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>5</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>6</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>7</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>8</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>9</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>10</strong></td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td style="text-align:center">&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <p><strong>Section THREE</strong></p>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Overall Work Performance</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>11</strong></td>
                                                    <td style="text-align:center"><strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Job Ethics&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Performance needs improvement to meed Job Requirements​.​​​​​​&nbsp;<input type="checkbox" /></strong></td>
                                                    <td><strong>Performance need Jobs Requirements.<input type="checkbox" /></strong></td>
                                                    <td><strong>Performance exceeds Job Requirements.</strong><input type="checkbox" /></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Scale of Marking</strong></td>
                                                    <td style="text-align:center">01</td>
                                                    <td style="text-align:center">02</td>
                                                    <td style="text-align:center">03</td>
                                                    <td style="text-align:center">04</td>
                                                    <td style="text-align:center">05</td>
                                                    <td style="text-align:center">06</td>
                                                    <td style="text-align:center">07</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">08</td>
                                                    <td style="text-align:center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                    <p>&nbsp;</p>

                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <p><strong>Section FOUR</strong></p>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>Employee&#39;s Comment</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                    <p style="text-align:center"><strong>Please write bellow any other information you would like to iform the management (Education/Training/any draw Back).</strong></p>

                                                    <p style="text-align:center"><strong>This section can be your delf evaluation fo your current job experionce/performance</strong></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center">
                                                    <p>&nbsp;</p>

                                                    <p style="text-align:right">&nbsp;</p>

                                                    <p style="text-align:right">&nbsp;</p>

                                                    <p style="text-align:right"><strong>Staff&#39;s Signature &amp; Date</strong></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>This Section Should be filled by Supervisor</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="3" rowspan="1">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
                                                    <td colspan="3" rowspan="1" style="text-align:center"><strong>Total Marks by Supervisor</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="1" rowspan="2" style="text-align:center"><strong>Result</strong></td>
                                                    <td style="text-align:center"><strong>Poor</strong></td>
                                                    <td style="text-align:center"><strong>Average</strong></td>
                                                    <td style="text-align:center"><strong>Above Average</strong></td>
                                                    <td style="text-align:center"><strong>High Average</strong></td>
                                                    <td style="text-align:center"><strong>Excellent</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>0-40</strong></td>
                                                    <td style="text-align:center"><strong>41-60</strong></td>
                                                    <td style="text-align:center"><strong>61-80</strong></td>
                                                    <td style="text-align:center"><strong>81-90</strong></td>
                                                    <td style="text-align:center"><strong>91-100</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Recommendation</strong></td>
                                                    <td style="text-align:center"><strong>Promotion</strong></td>
                                                    <td style="text-align:center"><strong>Not recommended for promotion</strong></td>
                                                    <td style="text-align:center"><strong>Only Increment</strong></td>
                                                    <td colspan="2" rowspan="1" style="text-align:center"><strong>Not Increment</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Increment</strong></td>
                                                    <td style="text-align:center"><strong>1-Step</strong></td>
                                                    <td style="text-align:center"><strong>2-Steps</strong></td>
                                                    <td style="text-align:center"><strong>3-Steps</strong></td>
                                                    <td colspan="2" rowspan="1" style="text-align:center"><strong>4-Steps</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" rowspan="2">
                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                    <td colspan="3" rowspan="2" style="text-align:center">
                                                    <p>&nbsp;</p>

                                                    <p><strong>Supervisor&#39;s Signature &amp; Date</strong></p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>This Section should be filled by Head of Department</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="3" rowspan="1">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
                                                    <td colspan="3" rowspan="1" style="text-align:center"><strong>Revised Marks by HOD</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="1" rowspan="2" style="text-align:center"><strong>Result</strong></td>
                                                    <td style="text-align:center"><strong>Poor</strong></td>
                                                    <td style="text-align:center"><strong>Average</strong></td>
                                                    <td style="text-align:center"><strong>Above Average</strong></td>
                                                    <td style="text-align:center"><strong>High Average</strong></td>
                                                    <td style="text-align:center"><strong>Excellent</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>0-40</strong></td>
                                                    <td style="text-align:center"><strong>41-60</strong></td>
                                                    <td style="text-align:center"><strong>61-80</strong></td>
                                                    <td style="text-align:center"><strong>81-90</strong></td>
                                                    <td style="text-align:center"><strong>91-100</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Recommendation</strong></td>
                                                    <td style="text-align:center"><strong>Promotion</strong></td>
                                                    <td style="text-align:center"><strong>Not recommended for promotion</strong></td>
                                                    <td style="text-align:center"><strong>Only Increment</strong></td>
                                                    <td colspan="2" rowspan="1" style="text-align:center"><strong>Not Increment</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Increment</strong></td>
                                                    <td style="text-align:center"><strong>1-Step</strong></td>
                                                    <td style="text-align:center"><strong>2-Steps</strong></td>
                                                    <td style="text-align:center"><strong>3-Steps</strong></td>
                                                    <td colspan="2" rowspan="1" style="text-align:center"><strong>4-Steps</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" rowspan="2">
                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                    <td colspan="3" rowspan="2" style="text-align:center">
                                                    <p>&nbsp;</p>

                                                    <p><strong>HOD Signature &amp; Date</strong></p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>This Section should be filled by Human Resource (HR)</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="3" rowspan="1">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
                                                    <td colspan="3" rowspan="1" style="text-align:center"><strong>Total Marks by HR</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="1" rowspan="2" style="text-align:center"><strong>Result</strong></td>
                                                    <td style="text-align:center"><strong>Poor</strong></td>
                                                    <td style="text-align:center"><strong>Average</strong></td>
                                                    <td style="text-align:center"><strong>Above Average</strong></td>
                                                    <td style="text-align:center"><strong>High Average</strong></td>
                                                    <td style="text-align:center"><strong>Excellent</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>0-40</strong></td>
                                                    <td style="text-align:center"><strong>41-60</strong></td>
                                                    <td style="text-align:center"><strong>61-80</strong></td>
                                                    <td style="text-align:center"><strong>81-90</strong></td>
                                                    <td style="text-align:center"><strong>91-100</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Recommendation</strong></td>
                                                    <td style="text-align:center"><strong>Promotion</strong></td>
                                                    <td style="text-align:center"><strong>Not recommended for promotion</strong></td>
                                                    <td style="text-align:center"><strong>Only Increment</strong></td>
                                                    <td colspan="2" rowspan="1" style="text-align:center"><strong>Not Increment</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Increment</strong></td>
                                                    <td style="text-align:center"><strong>1-Step</strong></td>
                                                    <td style="text-align:center"><strong>2-Steps</strong></td>
                                                    <td style="text-align:center"><strong>3-Steps</strong></td>
                                                    <td colspan="2" rowspan="1" style="text-align:center"><strong>4-Steps</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" rowspan="2">
                                                    <p><strong>Comments:</strong></p>

                                                    <p>&nbsp;</p>
                                                    </td>
                                                    <td colspan="3" rowspan="2" style="text-align:center">
                                                    <p>&nbsp;</p>

                                                    <p><strong>HR Signature &amp; Date</strong></p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><strong>This Section should be filled by Managing Director</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="3" rowspan="1">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
                                                    <td colspan="3" rowspan="1" style="text-align:center"><strong>Total Marks by Managing Director</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="1" rowspan="2" style="text-align:center"><strong>Result</strong></td>
                                                    <td style="text-align:center"><strong>Poor</strong></td>
                                                    <td style="text-align:center"><strong>Average</strong></td>
                                                    <td style="text-align:center"><strong>Above Average</strong></td>
                                                    <td style="text-align:center"><strong>High Average</strong></td>
                                                    <td style="text-align:center"><strong>Excellent</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>0-40</strong></td>
                                                    <td style="text-align:center"><strong>41-60</strong></td>
                                                    <td style="text-align:center"><strong>61-80</strong></td>
                                                    <td style="text-align:center"><strong>81-90</strong></td>
                                                    <td style="text-align:center"><strong>91-100</strong></td>
                                                </tr>
                                                <tr>
                                                </tr>
                                            </tbody>
                                        </table>
                                        </caption>
                                    </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>',
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'title' => 'IOU',
                'body' => '<div style="width:790px">
                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <tbody>
                                        <tr>
                                            <td style="text-align:center"><img alt="" src="/images/logo/flydubai.jpg" style="height:100px; width:300px" /></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table border="0" cellpadding="0" cellspacing="0" style="width:790px">
                                    <tbody>
                                        <tr>
                                            <td rowspan="1" style="text-align:center"><span style="font-size:24px"><strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong></span><u><span style="font-size:26px"><strong>IOU</strong></span></u></td>
                                            <td>
                                            <p>Ref:________</p>

                                            <p>Date:________</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p>Pyable to____________________________________________________________________ Staff No:___________________________________________________Amoount of _______________________________ required for the following purpose:</p>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <tbody>
                                        <tr>
                                            <td><strong>SL</strong></td>
                                            <td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
                                            <td style="text-align:center"><strong>Amount</strong></td>
                                            <td style="text-align:center"><strong>Total</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>1</strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td><strong>2</strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td><strong>3</strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td><strong>4</strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td><strong>5</strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" rowspan="1" style="text-align:right"><strong>Total</strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p>&nbsp;</p>

                                <p>&nbsp;</p>

                                <table border="0" cellpadding="0" cellspacing="0" style="width:790px">
                                    <tbody>
                                        <tr>
                                            <td style="text-align:center">
                                            <p>&nbsp;</p>

                                            <p><strong>Prepared by</strong></p>
                                            </td>
                                            <td style="text-align:center">
                                            <p>&nbsp;</p>

                                            <p><strong>Recommended By</strong></p>
                                            </td>
                                            <td style="text-align:center">
                                            <p>&nbsp;</p>

                                            <p><strong>Approved by Finance</strong></p>
                                            </td>
                                            <td style="text-align:center">
                                            <p>&nbsp;</p>

                                            <p><strong>Approved by Managing Director</strong></p>
                                            </td>
                                            <td style="text-align:center">
                                            <p>&nbsp;</p>

                                            <p><strong>Received By Staff</strong></p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p>&nbsp;</p>
                                </div>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'LEAVE APPLICATION FORM',
                'body' => '<div style="width:790px">
                                <h1 style="text-align:center"><span style="font-family:Comic Sans MS,cursive"><img alt="" src="/images/logo/flydubai.jpg" style="height:100px; margin:0px; width:300px" />&nbsp;</span></h1>

                                <p style="text-align:center"><span style="font-size:14px"><span style="font-family:Comic Sans MS,cursive"><strong>Bashati Avenue, Plot-10, Unit C-4, Road#53, Gulshan Avenue,</strong></span></span></p>

                                <p style="text-align:center"><span style="font-size:14px"><span style="font-family:Comic Sans MS,cursive"><strong>Gulshan-2, Dhaka-1212, Bangladesh</strong></span></span></p>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <tbody>
                                        <tr>
                                            <th style="text-align:center"><span style="font-family:Comic Sans MS,cursive"><strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp;LEAVE APPLICATION FOR</strong></span></th>
                                            <td style="text-align:center">Date: 12.12.25</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <caption>&nbsp;</caption>
                                    <tbody>
                                        <tr>
                                            <td colspan="3" style="text-align:center"><strong>TO&nbsp;BE FILLED BY APPLICANT</strong>&nbsp;&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>&nbsp;Name:</strong></td>
                                            <td><strong>&nbsp;Designation:</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>&nbsp;DOJ:</strong></td>
                                            <td><strong>&nbsp;Staff ID:</strong></td>
                                            <td>&nbsp;<strong>Department:</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align:center"><strong>LEAVE PERIOD</strong></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <tbody>
                                        <tr>
                                            <td style="text-align:center"><strong>FORM</strong></td>
                                            <td style="text-align:center"><strong>TO</strong></td>
                                            <td style="text-align:center"><strong>TOTAL DAY&#39;s</strong></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center">&nbsp;</td>
                                            <td style="text-align:center">&nbsp;</td>
                                            <td style="text-align:center">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center">&nbsp;</td>
                                            <td style="text-align:center">&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <caption><strong>DESCRIPTION OF LEAVE</strong></caption>
                                    <tbody>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td style="text-align:center"><strong>REASON FOR LEAVE</strong></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center">[&nbsp; ] Anuual&nbsp; &nbsp; &nbsp; &nbsp;[&nbsp; ] Casual&nbsp; &nbsp; [&nbsp; ]&nbsp;Sick<br />
                                            <br />
                                            [&nbsp; ]&nbsp;Maternity&nbsp; &nbsp; &nbsp; [&nbsp; ] Unpaid&nbsp; &nbsp; &nbsp;[&nbsp; ] Others ..........</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <caption><strong>ADDRESS &amp; CONTACT WHILE ON LEAVE</strong></caption>
                                    <tbody>
                                        <tr>
                                            <td>
                                            <p>&nbsp;Remarks of Staff:</p>

                                            <p>&nbsp;</p>

                                            <p>&nbsp;Signature of Applicant</p>
                                            </td>
                                            <td>
                                            <p>Remarks of HOD:</p>

                                            <p>&nbsp;</p>

                                            <p>Signature of HOD</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <caption><strong>HR Department</strong></caption>
                                    <tbody>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td><strong>Annual</strong></td>
                                            <td><strong>Casual</strong></td>
                                            <td><strong>Sick</strong></td>
                                            <td><strong>Maternity</strong></td>
                                            <td><strong>Unpaid</strong></td>
                                            <td><strong>Others</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Leave Due</strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Leave Requested For</strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Balance</strong></td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <tbody>
                                        <tr>
                                            <td>
                                            <p>&nbsp; &nbsp; Enclosed Papers: [<input checked="checked" type="checkbox" />]</p>

                                            <p>&nbsp; &nbsp; [&nbsp; ]&nbsp; Medical&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;[&nbsp; ] Exam&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [&nbsp; ] Marriage&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [&nbsp; ] Family&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;[&nbsp; ] Tour&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;[&nbsp; ] Other</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <tbody>
                                        <tr>
                                            <td>&nbsp;HR Remarks:&nbsp;</td>
                                            <td>
                                            <p>&nbsp;</p>

                                            <p>&nbsp;</p>

                                            <p>&nbsp;</p>

                                            <p>&nbsp; &nbsp;Signature of HR</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                    <tbody>
                                        <tr>
                                            <td><strong>MD&#39;s Remarks:</strong></td>
                                            <td>
                                            <p><strong>[&nbsp; ] Approved</strong></p>

                                            <p>&nbsp;</p>

                                            <p>&nbsp;</p>

                                            <p>&nbsp;</p>

                                            <p><strong>&nbsp;Signature of Managing Director</strong></p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p>&nbsp;</p>
                                </div>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mobile Bill Limit',
                'body' => '<div style="font-family:Arial,sans-serif; text-align:center; width:760px">
                        <p><strong><span style="font-size:20px"><img alt="Sky Aviation Services Ltd." src="/images/logo/flydubai.jpg" style="height:140px; width:370px" /></span></strong></p>

                        <h2><strong>Mobile Bill Limit</strong></h2>

                        <p style="margin-right:150px; text-align:right"><strong>Dated:</strong></p>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left"><strong>Staff Name:&nbsp;</strong></td>
                                    <td style="text-align:left"><strong>Staff No:</strong></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left"><strong>Designation:</strong></td>
                                    <td style="text-align:left"><strong>DOJ:</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:left"><strong>Department:</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:left"><strong>Station:</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left"><strong>Present Authorized limit BDT:</strong></td>
                                    <td style="text-align:left"><strong>Requesting BDT:</strong></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left"><strong>Does it include internet (Yes/No):</strong></td>
                                    <td style="text-align:left"><strong>Do you require internet (Yes/No):</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="height:50px; text-align:left"><strong>If yes, give reason:</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <h3><strong><u>HRD</u></strong></h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left"><strong>Limit Increase (Recommended / Not Recommended):</strong></td>
                                    <td style="text-align:left"><strong>Increase Limit to BDT:</strong></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left"><strong>Internet (Yes/No):</strong></td>
                                    <td style="text-align:left"><strong>Data Limit:</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:left"><strong>Total Price:</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <h3><strong><u>Management</u></strong></h3>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left"><strong>Authorization (Approved/Not Approved):</strong></td>
                                    <td style="text-align:left"><strong>Limit to BDT:</strong></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left"><strong>Internet (Approved/Not Approved):</strong></td>
                                    <td style="text-align:left"><strong>Data Limit:</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 style="text-align:left"><strong>Note:</strong></h3>

                        <ol>
                            <li style="text-align:left">All staffs using official call, must exercise caution &amp; not use the mobile for personal call.</li>
                            <li style="text-align:left">Staff must justify the reason for any limit increase.</li>
                            <li style="text-align:left">Staffs using the mobile within approval limit for 3 months consecutively will be rewarded as per grade position.</li>
                        </ol>

                        <p style="text-align:left">&nbsp;</p>

                        <p style="text-align:left">&nbsp;</p>

                        <table cellspacing="0" style="border-collapse:collapse; width:100%">
                            <tbody>
                                <tr>
                                    <td style="border-style:none; text-align:left">
                                    <p style="margin-left:40px"><strong>Signature of Staff</strong></p>
                                    </td>
                                    <td style="border-style:none; text-align:center"><strong>HRD</strong></td>
                                    <td style="border-style:none; text-align:right">
                                    <p style="margin-right:40px"><strong>Approved By</strong></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>',
                'created_at' => now(),
                'updated_at'=> now(),
            ],
            [
                'title'=> 'Official e-mail Account',
                'body'=> '<div style="font-family:Arial,sans-serif; text-align:center; width:750px">
                        <p><strong><span style="font-size:20px"><img alt="Sky Aviation Services Ltd." src="/images/logo/flydubai.jpg" style="height:140px; width:370px" /></span></strong></p>

                        <h2 style="text-align:center"><strong>GSA &ndash; Flydubai</strong></h2>

                        <h2 style="text-align:center"><strong>Form for official e-mail Account</strong></h2>

                        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; width:100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:left"><strong>Name of the Employee:</strong></td>
                                    <td style="text-align:left"><strong>Staff No:</strong></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left"><strong>Designation:</strong></td>
                                    <td style="text-align:left"><strong>Date of Joining:</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:left"><strong>Location:</strong></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left"><strong>Proposed official e-mail:</strong></td>
                                    <td style="text-align:left"><strong>Personal e-mail:</strong></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left"><strong>Contact (Official):<br />
                                    Ph:<br />
                                    Mob:</strong></td>
                                    <td style="text-align:left"><strong>Contact (Personal):<br />
                                    Home:<br />
                                    Cell:</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <p>&nbsp;</p>

                        <p style="margin-right:20%; text-align:right"><strong>Signature of Staff</strong></p>

                        <p style="margin-right:20%; text-align:right">&nbsp;</p>

                        <p>&nbsp;</p>

                        <table cellspacing="0" style="border-collapse:collapse; width:100%">
                            <tbody>
                                <tr>
                                    <td style="border-style:none; text-align:center"><strong>Department Head Signature</strong></td>
                                    <td style="border-style:none; text-align:center"><strong>Approved By Signature</strong></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>',
                'created_at' => now(),
                'updated_at'=> now(),
            ],
            [
                'title'=> 'Payment Voucher',
                'body' => '<div style="font-family:Arial,sans-serif; text-align:center; width:750px">
                            <p><strong><span style="font-size:20px"><img alt="Sky Aviation Services Ltd." src="/images/logo/flydubai.jpg" style="height:140px; width:370px" /></span></strong></p>

                            <h2><u>PAYMENT VOUCHER</u></h2>

                            <p>Location:</p>

                            <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; width:100%">
                                <thead>
                                    <tr>
                                        <th style="text-align:left">Date</th>
                                        <th style="text-align:left">Voucher No</th>
                                        <th style="text-align:left">ByCash/Cheque No</th>
                                        <th style="text-align:left">Bank</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>

                            <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                                <thead>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="text-align:left">Payment Made to</td>
                                    </tr>
                                </tbody>
                            </table>

                            <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Description</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Dept.</th>
                                        <th colspan="3">Amount</th>
                                    </tr>
                                    <tr>
                                        <th>Curr:</th>
                                        <th>BDT</th>
                                        <th>Ps.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                        <td style="text-align:left">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align:left">Amount in words:</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="height:50px; text-align:left">Being:</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p style="margin-right:150px; text-align:right">Payment Receive:</p>

                            <p style="text-align:left">&nbsp;</p>

                            <p style="text-align:left">&nbsp;</p>

                            <table cellspacing="0" style="border-collapse:collapse; width:100%">
                                <tbody>
                                    <tr>
                                        <td style="border-style:none; text-align:center">Prepared by</td>
                                        <td style="border-style:none; text-align:center">Checked By</td>
                                        <td style="border-style:none; text-align:center">Finance</td>
                                        <td style="border-style:none; text-align:center">Managing Director</td>
                                        <td style="border-style:none; text-align:center">Signature</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>',
                'created_at' => now(),
                'updated_at'=> now(),
            ],
            [
                'title'=> 'Rebate Application Form',
                'body'=> '<table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                <tbody>
                                    <tr>
                                        <td>
                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><span style="font-family:Comic Sans MS,cursive"><strong><span style="font-size:24px"><img alt="" src="/images/logo/flydubai.jpg" style="height:100px; width:402px" /></span></strong></span></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center"><span style="font-size:22px"><span style="font-family:Comic Sans MS,cursive"><strong>Rebate Application Form</strong></span></span></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Rebate Application for Travel on</strong></td>
                                                    <td>&nbsp; &nbsp; &nbsp;</td>
                                                    <td><strong>FZ if OAL then mention Carrier :</strong></td>
                                                    <td>&nbsp; &nbsp; &nbsp;&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="6" rowspan="1" style="text-align:center"><strong>Personal Information</strong></td>
                                                    <td style="text-align:center"><strong>Date</strong></td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>1. Staff Name:</strong></td>
                                                    <td colspan="5" rowspan="1">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</td>
                                                    <td style="text-align:center"><strong>2. Staff ID:</strong></td>
                                                    <td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>2. Designation:</strong></td>
                                                    <td colspan="5" rowspan="1">&nbsp;</td>
                                                    <td style="text-align:center">4. DOJ:</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>3. Depertment</strong></td>
                                                    <td colspan="3" rowspan="1">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
                                                    <td><strong>6. Station</strong></td>
                                                    <td colspan="3" rowspan="1">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>7. Location:</strong></td>
                                                    <td colspan="7" rowspan="1">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>8. Type of Travel</strong></td>
                                                    <td><input type="checkbox" /><strong>ID00</strong></td>
                                                    <td><input type="checkbox" /><strong>ID90</strong></td>
                                                    <td><input type="checkbox" /><strong>ID50</strong></td>
                                                    <td><input type="checkbox" /><strong>ID75</strong></td>
                                                    <td><strong>9. Passport Number</strong></td>
                                                    <td colspan="2" rowspan="1">&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="2" style="text-align:center"><strong>Travel Information</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>10. Travel Details (Sector):</strong></td>
                                                    <td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>11. Date of Travel:</strong></td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>12. Date of Return:</strong></td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>13. Flight Details:</strong></td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>14. Baggage Requirement</strong></td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>15. Previous Details of Rebate on FZ of OAL:</strong></td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="4" style="text-align:center"><strong>16. Passengers Name</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center"><strong>Name</strong></td>
                                                    <td style="text-align:center"><strong>Relationship</strong></td>
                                                    <td style="text-align:center"><strong>Age of Children</strong></td>
                                                    <td style="text-align:center"><strong>Passport Number</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>a.</strong></td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>b.</strong></td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>c.</strong></td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>d.</strong></td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>e.</strong></td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4"><strong>17. Remarks</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</td>
                                                    <td colspan="3" rowspan="1">
                                                    <p>&nbsp;</p>

                                                    <p style="text-align:center"><strong>Signature of Staff</strong></p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table border="1" cellpadding="1" cellspacing="1" style="width:790px">
                                            <tbody>
                                                <tr>
                                                    <td colspan="5" style="text-align:center"><strong>Office Use Only</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="1" rowspan="3"><strong>18. Rebate Approval:</strong></td>
                                                    <td><strong><input type="checkbox" />Approved</strong></td>
                                                    <td><strong><input type="checkbox" />ID00</strong></td>
                                                    <td><strong><input type="checkbox" />ID90</strong></td>
                                                    <td><strong><input type="checkbox" />ID75</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong><input type="checkbox" />Not Approved</strong></td>
                                                    <td><strong>If Approved, PNR</strong></td>
                                                    <td colspan="2" rowspan="1">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>19. Special Instruction:</strong></td>
                                                    <td colspan="3" rowspan="1">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>20. HR Comments:</strong></td>
                                                    <td colspan="4" rowspan="1">&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <p style="text-align:center">&nbsp;</p>

                                        <p style="text-align:center">&nbsp;</p>

                                        <p style="text-align:center">Checked By HR&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Apprived By Managing Director</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>',
                'created_at' => now(),
                'updated_at'=> now(),
            ],
            [
                'title'=> 'Staff Appraisal',
                'body'=> '<div style="font-family:Arial,sans-serif; text-align:center; width:750px">
                    <p><strong><span style="font-size:20px"><img alt="Sky Aviation Services Ltd." src="/images/logo/flydubai.jpg" style="height:140px; width:370px" /></span></strong></p>

                    <h2><u><span style="font-size:20px"><strong><span dir="ltr" lang="EN-GB"><span style="font-family:&quot;Calibri&quot;,sans-serif">Staff Appraisal</span></span></strong></span></u></h2>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <tbody>
                            <tr>
                                <td style="text-align:left; width:20%">Staff Name/number:</td>
                                <td style="text-align:left; width:30%">&nbsp;</td>
                                <td style="text-align:left; width:20%">Station:</td>
                                <td style="text-align:left; width:30%">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left; width:20%">Title/position:</td>
                                <td style="text-align:left; width:30%">&nbsp;</td>
                                <td style="text-align:left; width:20%">Department:</td>
                                <td style="text-align:left; width:30%">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left; width:20%">Year:</td>
                                <td style="text-align:left; width:30%">&nbsp;</td>
                                <td style="text-align:left; width:20%">Line Manager Name:</td>
                                <td style="text-align:left; width:30%">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <tbody>
                            <tr>
                                <td style="text-align:left; width:50%">Strengths</td>
                                <td style="text-align:left; width:50%">Aspirations</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">
                                <p>Highlight your key strengths.&nbsp;Include areas of knowledge and experience as well as Leadership skills. <span style="color:red">(3 points enough)</span></p>
                                </td>
                                <td style="text-align:left">Describe professional interests/career aspirations both short-term and long-term.&nbsp; Consider company direction and work team needs.&nbsp; Identify any constraints and discuss these with your manager.&nbsp;<span style="color:red">(3 points enough)</span></td>
                            </tr>
                            <tr>
                                <td style="text-align:left">
                                <ul>
                                    <li>&nbsp;</li>
                                    <li>&nbsp;</li>
                                    <li>&nbsp;</li>
                                </ul>
                                </td>
                                <td style="text-align:left">
                                <ul>
                                    <li>&nbsp;</li>
                                    <li>&nbsp;</li>
                                    <li>&nbsp;</li>
                                </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:left">Part A: Annual Review of Performance Objectives</td>
                            </tr>
                        </tbody>
                    </table>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <tbody>
                            <tr>
                                <td style="text-align:center; width:30%">Objectives</td>
                                <td style="text-align:center; width:20%">Critical Objectives</td>
                                <td style="text-align:center; width:50%">How to Accomplish?</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">List 2024 objectives below. What do you need to accomplish this year to meet our business needs?&nbsp;<span style="color:red">(3 points enough)</span></td>
                                <td style="text-align:left">Identify (X) up to 3 critical objectives.</td>
                                <td style="text-align:left">In preparation for annual reviews, describe your progress to date towards meeting each objective. Give clear examples of how you accomplished your objective and what impact this had.&nbsp;<span style="color:red">(3 points enough)</span></td>
                            </tr>
                            <tr>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <tbody>
                            <tr>
                                <td style="text-align:center; width:30%">Learning Points</td>
                                <td style="text-align:center; width:70%">Development Actions &amp; Achievements</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">List Learning Points below. What have you identified as your key areas of development for the future?</td>
                                <td style="text-align:left">Outline how you will address your learning point.&nbsp;<span style="color:red">(2&nbsp;points enough)</span></td>
                            </tr>
                            <tr>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:left">
                                <p>Part B: Annual Review of Performance Competencies &nbsp;&nbsp;&nbsp;<span style="color:red">Rating: A: Excellent B: Very Good C: Good D: Acceptable U: Unacceptable</span></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <tbody>
                            <tr>
                                <td style="text-align:center; width:5%">No</td>
                                <td style="text-align:center; width:35%">Competency</td>
                                <td style="text-align:center; width:10%">Rating</td>
                                <td style="text-align:center; width:50%">Manager&rsquo;s comment</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">1</td>
                                <td style="text-align:left">Problem Solving &amp; challenge unlocking</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">2</td>
                                <td style="text-align:left">Decision Making</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">3</td>
                                <td style="text-align:left">Collaboration &amp; Team work</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">4</td>
                                <td style="text-align:left">Work Ethic &amp; Standards</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">5</td>
                                <td style="text-align:left">Management of time &amp; task</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">6</td>
                                <td style="text-align:left">Focus &amp; attention to details</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">7</td>
                                <td style="text-align:left">Ambition &amp; motivation</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">8</td>
                                <td style="text-align:left">Punctuality &amp; Discipline</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">9</td>
                                <td style="text-align:left">Adaptability &amp; flexibility</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">10</td>
                                <td style="text-align:left">Skills (Technical, Interpersonal, communication)</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">11</td>
                                <td style="text-align:left">Commitment to KPIs</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:left">12</td>
                                <td style="text-align:left">Adherence to instructions</td>
                                <td style="text-align:left">&nbsp;</td>
                                <td style="text-align:left">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:left">Part C: Overall Annual Review of Performance (Objectives + Competencies)</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="text-align:left">Employee&rsquo;s Summary of Overall Performance</p>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="height:50px; text-align:left">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="text-align:left">Manager Summary of Overall Performance</p>

                    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:10px; width:100%">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="height:50px; text-align:left">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>

                    <p style="text-align:left">My manager and I have discussed this annual performance review and jointly prepared this document.</p>

                    <p style="text-align:left">My signature does not necessarily signify agreement or disagreement.</p>

                    <p style="text-align:left">&nbsp;</p>

                    <p style="text-align:left">&nbsp;</p>

                    <table cellspacing="0" style="border-collapse:collapse; width:100%">
                        <tbody>
                            <tr>
                                <td style="border-style:none; text-align:left; width:30%">Employee&rsquo;s Signature:.........................</td>
                                <td style="border-style:none; text-align:left; width:20%">Date: ..............................</td>
                                <td style="border-style:none; text-align:left; width:30%">Manager&rsquo;s Signature: ..........................</td>
                                <td style="border-style:none; text-align:left; width:20%">Date: ...............................</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>',
                'created_at' => now(),
                'updated_at'=> now(),
            ],
            [
                'title'=> 'Station Leave Authorization',
                'body'=> '<div style="font-family:Arial,sans-serif; text-align:center; width:750px">
                        <p><strong><span style="font-size:20px"><img alt="Sky Aviation Services Ltd." src="/images/logo/flydubai.jpg" style="height:140px; width:370px" /></span></strong></p>

                        <h2>Station Leave Authorization Form</h2>

                        <p style="margin-right:150px; text-align:right">Dated:</p>

                        <table border="1" cellpadding="1" cellspacing="0" style="width:100%">
                            <tbody>
                                <tr>
                                    <td>
                                    <p style="margin-left:20px; text-align:left">Staff Name: ..........................................................................&nbsp;Staff No: .....................................................................................</p>

                                    <p style="margin-left:20px; text-align:left">Designation: ........................................................................&nbsp; DOJ: ..........................................................................................</p>

                                    <p style="margin-left:20px; text-align:left">Depertment: ........................................................................&nbsp; Station: ......................................................................................</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="1" cellspacing="0" style="margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td>
                                    <p style="margin-left:20px; text-align:left">7. Station leave permission required from: ................................................&nbsp;to ........................................................................</p>

                                    <p style="margin-left:20px; text-align:left">8. Present address: .................................................................................................................................................................</p>

                                    <p style="margin-left:20px; text-align:left">.................................................................................................................................................................................................</p>

                                    <p style="margin-left:20px; text-align:left">9. Address during out of station: ............................................................................................................................................</p>

                                    <p style="margin-left:20px; text-align:left">...............................................................................................................................................................................................</p>

                                    <p style="margin-left:20px; text-align:left">10. Contact numbers Cell: ..............................................................&nbsp;Alternative No: .............................................................</p>

                                    <p style="margin-left:20px; text-align:left">11. Reason for Station Leave: ..............................................................................................................................................</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table border="1" cellpadding="1" cellspacing="0" style="margin-top:10px; width:100%">
                            <tbody>
                                <tr>
                                    <td>
                                    <p style="margin-left:20px; text-align:left">12. Remarks: Any other infcrrnation the staff wants the company to know: .........................................................................</p>

                                    <p style="margin-left:20px; text-align:left">..............................................................................................................................................................................................</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p>&nbsp;</p>

                        <p>&nbsp;</p>

                        <div style="margin-top:30px">
                        <div style="float:left; text-align:center; width:24%">Signature of staff</div>

                        <div style="float:left; text-align:center; width:24%">Signature of HOD</div>

                        <div style="float:left; text-align:center; width:24%">Signature of HR</div>

                        <div style="float:left; text-align:center; width:24%">Approved by</div>

                        <div style="clear:both">&nbsp;</div>
                        </div>
                        </div>',
                'created_at' => now(),
                'updated_at'=> now(),
            ]
        ];

        DB::table('official_forms')->insert($data);
//        dd("Done");
    }
}
