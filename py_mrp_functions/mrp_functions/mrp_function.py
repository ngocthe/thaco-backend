from datetime import datetime
from typing import Tuple, Any, List
import numpy as np
import pandas as pd
from mrp_functions import logging
from mrp_functions.base_tasks import TaskBase


def calculate_mrp_result(msc_code, production_plan_id, mrp_run_date, plant_code,
                         each_msc_parts_need_frame: pd.DataFrame,
                         production_volume: pd.DataFrame,
                         tb: TaskBase) -> pd.DataFrame:
    logging.info(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}] "
                 f"MSC Parts Size: {len(each_msc_parts_need_frame)}")

    if len(each_msc_parts_need_frame) == 0:
        logging.info(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}]"
                     f" BOM Parts of MSC not found !")
        return None, None
    else:
        logging.debug(f"[{msc_code}] MSC Parts:\n"
                      f"{each_msc_parts_need_frame.to_dict(orient='records')}")

    if len(production_volume) == 0:
        logging.info(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}]"
                     f" Production Volume of MSC not found !")
        return None, None
    else:
        logging.debug(f"[{msc_code}] Shortage Parts:\n"
                      f"{each_msc_parts_need_frame.to_dict(orient='records')}")

    # Filter Part Color Code XX
    parts_with_color_xx = each_msc_parts_need_frame.loc[each_msc_parts_need_frame['part_color_code'] == 'XX']
    # Find and replace Color XX with Color Code found in Database
    msc_list_distinct_vehicle_color = production_volume.vehicle_color_code.unique().tolist()
    each_msc_parts_need_frame['vehicle_color_code'] = np.NaN
    for part_color_xx_idx in parts_with_color_xx.index:
        for vehicle_color_code in msc_list_distinct_vehicle_color:
            part_code = parts_with_color_xx.loc[part_color_xx_idx, ['part_code']].item()
            color_code_for_xx = tb.query_part_color_xx(part_code, vehicle_color_code, plant_code)
            if color_code_for_xx is not None:
                # Assign color code for XX
                each_msc_parts_need_frame.loc[part_color_xx_idx,
                                              ['part_color_code',
                                               'vehicle_color_code']] = color_code_for_xx, vehicle_color_code

    volume_parts_list_2 = []
    for idx in production_volume.index:
        row = production_volume.iloc[idx]
        vf = each_msc_parts_need_frame.loc[((pd.isna(each_msc_parts_need_frame['bom_ecn_in_date'])) | (
                each_msc_parts_need_frame['bom_ecn_in_date'] <= row.plan_date)) &
                                           ((pd.isna(each_msc_parts_need_frame['bom_ecn_out_date'])) | (
                                                   each_msc_parts_need_frame['bom_ecn_out_date'] >= row.plan_date)) &
                                           ((pd.isna(each_msc_parts_need_frame['part_ecn_in_date'])) | (
                                                   each_msc_parts_need_frame['part_ecn_in_date'] <= row.plan_date)) &
                                           ((pd.isna(each_msc_parts_need_frame['part_ecn_out_date'])) | (
                                                   each_msc_parts_need_frame['part_ecn_out_date'] > row.plan_date)),
                                           ["msc_code", "part_code", "part_color_code", "part_quantity",
                                            "plant_code"]].copy(deep=True)
        if isinstance(vf, pd.Series):
            vf['production_date'] = row.plan_date
            vf['vehicle_color_code'] = row.vehicle_color_code
            vf['production_volume'] = row.volume
            vf['part_requirement_quantity'] = vf['part_quantity'] * row.volume
            list_f = [vf.to_dict()]
        else:
            vf['part_requirement_quantity'] = vf[['part_quantity']] * row.volume
            vf.loc[:, ['production_date']] = row.plan_date
            vf.loc[:, ['vehicle_color_code']] = row.vehicle_color_code
            vf.loc[:, ['production_volume']] = row.volume
            list_f = vf.to_dict(orient='records')
        volume_parts_list_2.extend(list_f)

    if len(volume_parts_list_2) == 0:
        logging.info(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}]"
                     f" Volume to Parts Lists of MSC empty !")
        return None

    logging.info(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}] "
                 f"Volume to Parts Lists Size: {len(volume_parts_list_2)}")

    # Convert List of Series to DataFrame
    mrp_parts_quantity = pd.DataFrame(volume_parts_list_2)
    mrp_parts_quantity = mrp_parts_quantity.loc[~mrp_parts_quantity.duplicated(keep='first')]
    mrp_parts_quantity[['import_id']] = production_plan_id

    return mrp_parts_quantity
