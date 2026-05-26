import AdminLayout from "@/Layouts/AdminLayout";
import { Head } from "@inertiajs/react";
import React from "react";
import {useState} from "react"
import { useForm } from '@inertiajs/react'

import { Link } from "@inertiajs/react";

import ArrowCircleLeftRoundedIcon from "@mui/icons-material/ArrowCircleLeftRounded";
import IconButton from "@mui/material/IconButton";
import Tooltip from "@mui/material/Tooltip";
import { useTranslation } from "react-i18next";
import Box from '@mui/material/Box';
import FormGroup from '@mui/material/FormGroup';
import FormControl from '@mui/material/FormControl';
import InputLabel from "@mui/material/InputLabel";
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import Button from "@mui/material/Button";
import Alert from "@/Components/Alert";
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.tz.setDefault("America/Caracas"); 

export default function EmployeeBenefitHistoryCreate({ auth, employees }) {
    const { t } = useTranslation(["common"]);
    const [showAlert, setShowAlert] = useState(false)
    const { data, setData, patch, post, processing, errors } = useForm({
        employee:employees[0].id,
        benefit:employees[0].staff.benefits[0].id,
        request_date:dayjs.tz(new Date()),
        start_date: dayjs.tz(new Date()),
        end_date: dayjs.tz(new Date()),
        state:'draft'
    })
    
    const handleDate = (newDate,key) => {
       setData(prevData => ({
        ...prevData,
        [key]: dayjs(newDate).subtract(4,'hour')
       }))
    }

    const handleSelectChange = (e) => {
        const key   = e.target.name;
        const value = Number(e.target.value);

        setData(prevData => ({
                ...prevData,
                [key]: value,
            }))

    }

    const handleSubmit = (e) => {
        e.preventDefault();

        console.log(data)
        
        post(route("employee.benefit.history.store"));

    };

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


    return (
        <AdminLayout auth={auth}>
            {
                showAlert && (
                    <Alert
                        key={1}
                        message={"Cédula incorrecta"}
                        severity={"error"}
                    />
                )
            }

            <Head
                title={t("button.create field", {
                    field: "Solicitud",
                })}
            />

            <div className="flex justify-between items-center">
                <h2 className="text-xl text-gray-500">
                    {t("button.create field", {
                        field: "Solicitud",
                    })}
                </h2>
                <Link href={route("employee.benefit.history.index")}>
                    <Tooltip title={t("button.go back")}>
                        <IconButton size="large">
                            <ArrowCircleLeftRoundedIcon fontSize="inherit" />
                        </IconButton>
                    </Tooltip>
                </Link>
            </div>

            <Box
                component="form"
                sx={{ mt:2 }}
                onSubmit={handleSubmit}
            >
                <h4 className="">{printStateDescription(data.state)}</h4>
                <FormGroup sx={{ mt:2 }} >
                    <FormControl sx={{mt:2}} >
                        <InputLabel id={`employee-label`}>{'Empleado'}</InputLabel>
                        <Select
                            value={data.employee}
                            onChange={handleSelectChange}
                            labelId={`employee-label`}
                            id={'employee'}
                            name={'employee'}
                            sx={{m:1, maxWidth: 300 }} 
                        >
                            {
                                employees.map( (sv,_index) => (
                                    <MenuItem 
                                        key={_index}
                                        value={sv.id}
                                    >
                                        {`(${sv.cedula}) ${sv.name} ${sv.lastname}`}
                                    </MenuItem>
                                ) )
                            }
                        </Select>
                    </FormControl>

                    <FormControl sx={{mt:2}} >
                        <InputLabel id={`benefit-label`}>{'Beneficio'}</InputLabel>
                        <Select
                            value={data.benefit}
                            onChange={handleSelectChange}
                            labelId={`benefit-label`}
                            id={'benefit'}
                            name={'benefit'}
                            sx={{m:1, maxWidth: 300 }} 
                        >
                            {
                                employees.find(e => e.id == data.employee).staff.benefits.map( (sv,_index) => (
                                    <MenuItem 
                                        key={_index}
                                        value={sv.id}
                                    >
                                        {sv.name}
                                    </MenuItem>
                                ) )
                            }
                        </Select>
                    </FormControl>

                    <LocalizationProvider dateAdapter={AdapterDayjs}>
                        <DatePicker
                            label="Fecha de solicitud"
                            value={data.request_date}
                            id="request_date"
                            name="request_date"
                            format="D/M/YYYY"
                            onChange={(newDate) => handleDate(newDate, "request_date")}
                            sx={{m:1, maxWidth: 300 }}
                            required
                        />
                        <DatePicker
                            label="Desde"
                            value={data.start_date}
                            id="start_date"
                            name="start_date"
                            format="D/M/YYYY"
                            onChange={(newDate) => handleDate(newDate, "start_date")}
                            sx={{m:1, maxWidth: 300 }}
                            required
                        />
                        <DatePicker
                            label="Hasta"
                            value={data.end_date}
                            id="end_date"
                            name="end_date"
                            format="D/M/YYYY"
                            onChange={(newDate) => handleDate(newDate, "end_date")}
                            sx={{m:1, maxWidth: 300 }}
                            required
                        />
                    </LocalizationProvider>

                </FormGroup>

                <Button 
                    variant="contained" 
                    type="submit" 
                    disabled={processing} 
                    sx={{mt:2}} 
                >
                    {'Crear'}
                </Button>
            </Box>
        </AdminLayout>
    );
}