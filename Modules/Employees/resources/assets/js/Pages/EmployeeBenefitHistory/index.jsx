import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, usePage } from "@inertiajs/react";
import React from "react";
import Button from "@mui/material/Button";
import AddRoundedIcon from "@mui/icons-material/AddRounded";
import { useTranslation } from "react-i18next";
import EmployeeDropdownMenu from "../components/Dropdown";
import EmployeeRecordsTable from "../components/Table";


export default function EmployeeBenefitHistoryIndex({ auth, records, model, flash }) {
    const isAdmin = auth.permissions.find(
        (permission) => permission.name === "isAdmin"
    );
    const { t } = useTranslation(["common"]);

    const printStateDescription = state => {
        switch(state){
            case "draft": 
                return "Borrador"
            
            case "confirmed": 
                return "Confirmado"
            
            case "approved":
                return "Aprobado"
            
            case "rejected":
                return "Rechazado" 
        }
    } 

    const links = [
        {route:'employee.index',title:'Empleados'},
        {route:'employee.benefit.history.index',title:'Solicitudes de beneficios'}
    ]

    const formatDate = (_date) => {
        return `${(new Date(`${_date} 12:00:00`)).getDate()}-${(new Date(`${_date} 12:00:00`)).getMonth()+1}-${(new Date(`${_date} 12:00:00`)).getFullYear()}`
    }

    const tableHeaders = ['Empleado','Beneficio','Solicitado en','Aprobado en','Desde','Hasta','Estado']
    const tableRows = records.map( record => {
        return {
            id:record.id,
            name:`(${record.employee.cedula}) ${record.employee.name} ${record.employee.lastname}`,
            benefit:`(${record.employee.staff.name}) ${record.benefit.name}`,
            request_date: `${record.request_date ? formatDate(record.request_date) : ''}`,
            approvement_date: `${record.approvement_date ? formatDate(record.approvement_date) : 'N/A'}`,
            start_date: `${record.start_date ? formatDate(record.start_date) : 'N/A'}`,
            end_date: `${record.end_date ? formatDate(record.end_date) : 'N/A'}`,
            state: `${printStateDescription(record.state)}`
        }
    } )

    return (
        <AdminLayout auth={auth}>
            <Head title="Solicitudes de beneficios" />

            <EmployeeDropdownMenu links={links} auth={auth} model={'employee.benefit.history'} />

            <div className="flex justify-between items-center mt-5">
                <h2 className="text-xl text-gray-500"></h2>
                <Link href={route("employee.benefit.history.create")}>
                    <Button variant="contained" startIcon={<AddRoundedIcon />}>
                        {t("button.create field", {
                            field: t("Solicitud", { count: 1 }),
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
