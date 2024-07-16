#! /bin/bash

source base.sh

read -p "Branch [$branch_name]: " branch
branch=${branch:-$branch_name}

read -p "Github Username (not email): " username

git push https://$username@github.com/cilt-uct/tsugi-request-course-analytics-report.git $branch

if [ $? -eq 0 ]; then
  bash get.sh
fi
