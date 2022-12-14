<?php

namespace App\Imports;

use App\Models\{FederalEntity, Municipality, ZipCode, Settlement};
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Concerns\{ToCollection,WithHeadingRow};
use Illuminate\Support\Collection;

class DataImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(COllection $rows)
    {
        foreach($rows as $row)
        {
            if (!isset($row['c_estado'])) {echo "Warning: saltando hoja";continue;}
            try{

                $f = FederalEntity::firstOrCreate(['key' => $row['c_estado']],[
                    'name'=>$this->normalizeData($row['d_estado']),
                    'code'=>$row['c_cp']
                ]);

                $m = Municipality::firstOrCreate(['key' => $row['c_mnpio'], 'federal_entity_key'=>$f->key],[
                    'name'=>$this->normalizeData($row['d_mnpio']),
                ]);

                $z = ZipCode::firstOrCreate(['zip_code' => $row['d_codigo']],[
                    'locality'=>$this->normalizeData($row['d_ciudad']??""),
                    'municipality_id'=>$m->id,
                ]);

                Settlement::create([
                    'key' => $row['id_asenta_cpcons'],
                    'name'=>$this->normalizeData($row['d_asenta']),
                    'zone_type'=>$row['d_zona'],
                    'settlement_type'=>["name"=>$row['d_tipo_asenta']],
                    'zip_code_zip_code'=>$z->zip_code,
                ]);
            }catch(QueryException $e){
                dump($row);
                throw $e;
            }

        }
    }
    public function normalizeData($value)
    {
        $tildes= ['á'=>'a', 'é'=>'e','í'=>'i','ó'=>'o', 'ú'=>'u'];
        return strtoupper(str_replace(array_keys($tildes), array_values($tildes), strtolower($value)));
    }

}
