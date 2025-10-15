<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int|null $company
 * @property string $first_name
 * @property string $last_name
 * @property string|null $pers_code
 * @property string|null $photo
 * @property string|null $license_photo
 * @property string|null $medical_certificate_photo
 * @property string|null $medical_exam_passed
 * @property string|null $medical_exam_expired
 * @property string|null $citizenship
 * @property string|null $declared_country
 * @property string|null $declared_city
 * @property string|null $declared_street
 * @property string|null $declared_building
 * @property string|null $declared_room
 * @property string|null $declared_postcode
 * @property string|null $actual_country
 * @property string|null $actual_city
 * @property string|null $actual_street
 * @property string|null $actual_building
 * @property string|null $actual_room
 * @property string|null $phone
 * @property string|null $email
 * @property string $license_number
 * @property \Illuminate\Support\Carbon $license_issued
 * @property \Illuminate\Support\Carbon $license_end
 * @property string $code95_issued
 * @property string $code95_end
 * @property \Illuminate\Support\Carbon|null $permit_issued
 * @property \Illuminate\Support\Carbon|null $permit_expired
 * @property \Illuminate\Support\Carbon $medical_issued
 * @property \Illuminate\Support\Carbon $medical_expired
 * @property \Illuminate\Support\Carbon $declaration_issued
 * @property \Illuminate\Support\Carbon $declaration_expired
 * @property \App\Enums\DriverStatus $status
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $full_name
 * @property-read string $status_label
 * @method static \Database\Factories\DriverFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereActualBuilding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereActualCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereActualCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereActualRoom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereActualStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCitizenship($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCode95End($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCode95Issued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDeclarationExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDeclarationIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDeclaredBuilding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDeclaredCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDeclaredCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDeclaredPostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDeclaredRoom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDeclaredStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereLicenseEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereLicenseIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereLicensePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereMedicalCertificatePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereMedicalExamExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereMedicalExamPassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereMedicalExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereMedicalIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver wherePermitExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver wherePermitIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver wherePersCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereUpdatedAt($value)
 */
	class Driver extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $company
 * @property string $brand
 * @property string $plate
 * @property string $year
 * @property \Illuminate\Support\Carbon $inspection_issued
 * @property \Illuminate\Support\Carbon $inspection_expired
 * @property string $insurance_number
 * @property \Illuminate\Support\Carbon $insurance_issued
 * @property \Illuminate\Support\Carbon $insurance_expired
 * @property string $insurance_company
 * @property \Illuminate\Support\Carbon $tir_issued
 * @property \Illuminate\Support\Carbon $tir_expired
 * @property string $vin
 * @property string|null $tech_passport_nr
 * @property string|null $tech_passport_issued
 * @property string|null $tech_passport_expired
 * @property string|null $tech_passport_photo
 * @property int $status
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\TrailerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereInspectionExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereInspectionIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereInsuranceCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereInsuranceExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereInsuranceIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereInsuranceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer wherePlate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereTechPassportExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereTechPassportIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereTechPassportNr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereTechPassportPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereTirExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereTirIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereVin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trailer whereYear($value)
 */
	class Trailer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $company
 * @property string $brand
 * @property string $model
 * @property string $plate
 * @property int $year
 * @property \Illuminate\Support\Carbon $inspection_issued
 * @property \Illuminate\Support\Carbon $inspection_expired
 * @property string $insurance_number
 * @property \Illuminate\Support\Carbon $insurance_issued
 * @property \Illuminate\Support\Carbon $insurance_expired
 * @property string $insurance_company
 * @property string $vin
 * @property string|null $tech_passport_nr
 * @property string|null $tech_passport_issued
 * @property string|null $tech_passport_expired
 * @property string|null $tech_passport_photo
 * @property int $status
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $display_name
 * @method static \Database\Factories\TruckFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereInspectionExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereInspectionIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereInsuranceCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereInsuranceExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereInsuranceIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereInsuranceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck wherePlate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereTechPassportExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereTechPassportIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereTechPassportNr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereTechPassportPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereVin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Truck whereYear($value)
 */
	class Truck extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

