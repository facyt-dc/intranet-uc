import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, usePage } from "@inertiajs/react";
import React from "react";
import Button from "@mui/material/Button";
import AddRoundedIcon from "@mui/icons-material/AddRounded";
import { useTranslation } from "react-i18next";
import EmployeeDropdownMenu from "../components/Dropdown";
import EmployeeRecordsTable from "../components/Table";


export default function StaffIndex({ auth, staffs, model, flash }) {
    const isAdmin = auth.permissions.find(
        (permission) => permission.name === "isAdmin"
    );
    const { t } = useTranslation(["common"]);

    const links = [
        {route:'employee.staff.type.index',title:'Tipología de cargos'},
        {route:'employee.staff.index',title:'Cargos'},
        {route:'employee.benefit.index',title:'Beneficios'},
        {route:'employee.teaching.level.index',title:'Niveles de docencia'}
    ]

    const tableHeaders = ['Nombre','Tipo','Nro. puestos']
    const tableRows = staffs.map( staff => {
        return {
            id:staff.id,
            name:staff.name,
            type:staff.type.name,
            places_number: staff.places_number
        }
    } )

    return (
        <AdminLayout auth={auth}>
            <Head title="Cargos" />

            <EmployeeDropdownMenu links={links} auth={auth} model={'employee.staff'} />

            <div className="flex justify-between items-center mt-5">
                <h2 className="text-xl text-gray-500"></h2>
                <Link href={route("employee.staff.create")}>
                    <Button variant="contained" startIcon={<AddRoundedIcon />}>
                        {t("button.create field", {
                            field: t("cargo", { count: 1 }),
                        })}
                    </Button>
                </Link>
            </div>

            <EmployeeRecordsTable 
                headers={tableHeaders} 
                rows={tableRows} 
                model={model} 
                canDelete={true} 
                alert={flash?.alert}
                />
           
        </AdminLayout>
    );
}
