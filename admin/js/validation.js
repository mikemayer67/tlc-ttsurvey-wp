
export function survey_name(name) {
  if(name.length<4) { 
    return { ok:false, error:"too short" }; 
  }
  const m = name.match(/[^a-zA-Z0-9., -]/);
  if(m) {
    return { ok:false, error:`cannot contain '${m[0]}'` };
  }
  return {ok:true};
}
