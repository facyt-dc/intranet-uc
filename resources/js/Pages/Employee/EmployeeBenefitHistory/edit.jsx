import AdminLayout from "@/Layouts/AdminLayout";
import { Head } from "@inertiajs/react";
import React, { useEffect } from "react";
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

export default function EmployeeBenefitHistoryCreate({ auth, employees, history }) {
    const { t } = useTranslation(["common"]);
    const [showAlert, setShowAlert] = useState(false)
    const [hasClickConfirmButton, setHasClickConfirmButton] = useState(false)
    const [hasClickRejectButton, setHasClickRejectButton] = useState(false)
    const [hasClickApproveButton, setHasClickApproveButton] = useState(false)
    const { data, setData, patch, post, processing, errors } = useForm({
        employee:history.employee,
        benefit:history.benefit,
        request_date:dayjs.tz(history.request_date),
        approvement_date: dayjs.tz(history.approvement_date),
        start_date: dayjs.tz(history.start_date),
        end_date: dayjs.tz(history.end_date),
        state:history.state,
    })
    
    const handleDate = (newDate,key) => {
       setData(prevData => ({
        ...prevData,
        [key]: newDate
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
        
        patch(route("employee.benefit.history.update",history.id));

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

    const approveHistory = () => {
        setData(prevData => ({
            ...prevData,
            state: 'approved',
        }))

        setHasClickApproveButton(true)
    }

    const confirmHistory = () => {
        setData(prevData => {
            return {
                ...prevData,
                state: 'confirmed'
            }
        })

        setHasClickConfirmButton(true)
    }

    const rejectHistory = () => {
        setData(prevData => ({
            ...prevData,
            state: 'rejected'
        }))

        setHasClickRejectButton(true)
    }

    useEffect( () => {
        console.log(data)

        if(hasClickApproveButton || hasClickConfirmButton || hasClickRejectButton)
            patch(route("employee.benefit.history.update",history.id));

    }, [hasClickApproveButton, hasClickConfirmButton, hasClickRejectButton] )


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
                            disabled={data.state != 'draft'}
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
                            disabled={data.state != 'draft'} 
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
                            disabled={data.state != 'draft'}
                        />
                        <DatePicker
                            label="Fecha de aprobación"
                            value={data.approvement_date}
                            id="approvement_date"
                            name="approvement_date"
                            format="D/M/YYYY"
                            onChange={(newDate) => handleDate(newDate, "approvement_date")}
                            sx={{m:1, maxWidth: 300 }}
                            required
                            disabled={data.state != 'confirmed'}
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
                            disabled={data.state != 'draft'}
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
                            disabled={data.state != 'draft'}
                        />
                    </LocalizationProvider>

                </FormGroup>

                <Button 
                    variant="contained" 
                    type="submit" 
                    disabled={processing || data.state != 'draft'} 
                    sx={{mt:2}}
                >
                    {'Editar'}
                </Button>

                <Button 
                    variant="contained" 
                    type="button" 
                    disabled={data.state != 'draft'}
                    sx={{mt:2,ml:2,backgroundColor:'brown'}}
                    onClick={confirmHistory}
                >
                    {'Confirmar'}
                </Button>

                <Button 
                    variant="contained" 
                    type="button" 
                    disabled={data.state != 'confirmed'}
                    sx={{mt:2,ml:2,backgroundColor:'green'}} 
                    onClick={approveHistory}
                >
                    {'Aprobar'}
                </Button>

                <Button 
                    variant="contained" 
                    type="button" 
                    disabled={['approved','rejected'].includes(data.state)}
                    sx={{mt:2,ml:2,backgroundColor:'red'}} 
                    onClick={rejectHistory}
                >
                    {'Rechazar'}
                </Button>
            </Box>
        </AdminLayout>
    );
}