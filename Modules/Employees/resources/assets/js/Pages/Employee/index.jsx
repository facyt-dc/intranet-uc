import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, usePage } from "@inertiajs/react";
import React from "react";
import Button from "@mui/material/Button";
import AddRoundedIcon from "@mui/icons-material/AddRounded";
import { useTranslation } from "react-i18next";
import EmployeeDropdownMenu from "../components/Dropdown";
import EmployeeRecordsTable from "../components/Table";


export default function EmployeeIndex({ auth, employees, model, flash }) {
    const isAdmin = auth.permissions.find(
        (permission) => permission.name === "isAdmin"
    );
    const { t } = useTranslation(["common"]);

    const links = [
        {route:'employee.index',title:'Empleados'},
        {route:'employee.benefit.history.index',title:'Solicitudes de beneficios'}
    ]

    const tableHeaders = ['Nombre','Cargo']
    const tableRows = employees.map( EmployeeRecordForm => {
        return {
            id:EmployeeRecordForm.id,
            name:`${EmployeeRecordForm.name} ${EmployeeRecordForm.lastname}`,
            staff:EmployeeRecordForm.staff.name,
        }
    } )

    return (
        <AdminLayout auth={auth}>
            <Head title="Empleados" />

            <EmployeeDropdownMenu links={links} auth={auth} model={'employee'} />

            <div className="flex justify-between items-center mt-5">
                <h2 className="text-xl text-gray-500"></h2>
                <Link href={route("employee.create")}>
                    <Button variant="contained" startIcon={<AddRoundedIcon />}>
                        {t("button.create field", {
                            field: t("Empleado", { count: 1 }),
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
