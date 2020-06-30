'''Python script made to run on Rasberry PI, makes requests to "API" (not really) to check for
scheduled jobs and performs them asynchronously with specified in db delay
(by once again making requests).'''
import time
from threading import Thread
import requests

def async_job(job_delay, job_id, job_type):
    '''Makes request to "API" based on job_type. Works asynchronously so it wont stop the entire
    script form running.

    job_delay -> seconds of dealy b4 the job is performed.
    Negative values are also accepted but wont cause any delay.

    job_id -> id specifing details of job to be performed (ex. id of planet).

    job_type -> type of job (explained in db comment).'''
    print("Job Started, id:", job_id)
    if job_delay > 0:
        time.sleep(job_delay)

    if job_type == '0':
        page = "build_from_q.php"
        data = {'uname': 'nhi9fdujh4389fdjg89jkjkcGYFjn39hGHASDxzc',
                'pwd': '7db27gREBG83764gBHUEGjhadjbzxmbcnvqp1',
                'job_id': job_id}

    elif job_type == '1':
        page = "fleet_arrive_from_q.php"
        data = {'uname': 'AF94OKDmiodf3521301jxncaisd9xnadCKPL0',
                'pwd': 'qplxNMXCIU2474jMSKj9iS0PLAMXBXYWIQ3285',
                'job_id': job_id}

    elif job_type == '2':
        page = "build_ship_from_q.php"
        data = {'uname': 'lkjasdIU832jnv99SDIJF0asdk1AKJ0ojsdYDSJ01',
                'pwd': '4nzxm285ngyuHDK93jG783kjmxc0plqa1zXc',
                'job_id': job_id}

    job_req = requests.post("https://game.czerny.iq.pl/" + page, data=data)


    print(job_req.text)
    print("Job Ended, id:", job_id)

while 1:
    REQ = requests.post("https://game.czerny.iq.pl/rasberry_get_q.php",
                        data={'uname': 'HasUABSDBFUI324N32857JASDFasdf',
                              'pwd': 'JKHASGDbnsdf74oxmniwqe84ho98sfcfbnjkalsd'})
    # print(req.status_code, req.reason)
    RES = REQ.text
    if RES == "NO JOBS":
        print("No jobs...")
        # spacify here how often should the program make checks
        time.sleep(10)
    else:
        RES = RES.split("\t")
        DELAY = int(RES[3])
        Thread(target=async_job, args=(DELAY, RES[2], RES[1])).start()

        # remove job from q after scheduling it
        REQ_REMOVE = requests.post("https://game.czerny.iq.pl/rasberry_remove_q.php",
                                   data={'uname': 'jhq3485jjrng980masligUSF83rmnzmdsa',
                                         'pwd': 'USdbzxfasdfn83ldg380fnh327yfoenc73o0cnJ',
                                         'id': RES[0]})
